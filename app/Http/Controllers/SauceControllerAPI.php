<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sauce;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;
use Illuminate\Support\Facades\Storage;



/**
 * @OA\Schema(
 *     schema="Sauce",
 *     required={"name", "manufacturer", "description", "mainPepper", "heat", "image"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID de la sauce"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Nom de la sauce"
 *     ),
 *     @OA\Property(
 *         property="manufacturer",
 *         type="string",
 *         description="Nom du fabricant de la sauce"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Description de la sauce"
 *     ),
 *     @OA\Property(
 *         property="mainPepper",
 *         type="string",
 *         description="Principal ingrédient de la sauce"
 *     ),
 *     @OA\Property(
 *         property="heat",
 *         type="integer",
 *         description="Force de la sauce sur une échelle de 1 à 10"
 *     ),
 *     @OA\Property(
 *         property="imageUrl",
 *         type="string",
 *         description="URL de l'image de la sauce"
 *     ),
 *     @OA\Property(
 *         property="userId",
 *         type="integer",
 *         description="ID de l'utilisateur qui a créé la sauce"
 *     ),
 *     @OA\Property(
 *         property="likes",
 *         type="integer",
 *         description="Nombre de likes de la sauce"
 *     ),
 *     @OA\Property(
 *         property="dislikes",
 *         type="integer",
 *         description="Nombre de dislikes de la sauce"
 *     ),
 *     @OA\Property(
 *         property="usersLiked",
 *         type="array",
 *         @OA\Items(type="integer"),
 *         description="Tableau d'IDs des utilisateurs qui ont liké la sauce"
 *     ),
 *     @OA\Property(
 *         property="usersDisliked",
 *         type="array",
 *         @OA\Items(type="integer"),
 *         description="Tableau d'IDs des utilisateurs qui ont disliké la sauce"
 *     )
 * )
 */

class SauceControllerAPI extends Controller
{
    
    public function getUserFromToken()
    // Cette fonction permet de récupérer l'utilisateur à partir du token
    {
        
        try {
            $token = request()->bearerToken();
            return PersonalAccessToken::findToken($token)->tokenable;
        } catch (\Throwable $th) {
            return null;
        }

    }

    /**
     * @OA\Get(
     *     path="/api/sauces",
     *     tags={"Sauces"},
     *     security={{"bearerAuth":{}}},
     *     summary="Obtenir la liste de toutes les sauces",
     *     @OA\Response(response="200", description="Retourne un tableau de toutes les sauces")
     *     
     * )
     */
    public function index()
    {

        //On récupère l'utilisateur à partir du token
        if($this->getUserFromToken() == null){
            //On retourne une erreur 401 si l'utilisateur n'existe pas
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        //On retourne un json avec toutes les sauces de la base de données
        $listesSauce= Sauce::all();
        //Pour chacune des sauces on remplace les representants de tableau par des vrais tableaux
        foreach ($listesSauce as $sauce) {
            $sauce->usersLiked = json_decode($sauce->usersLiked);
            $sauce->usersDisliked = json_decode($sauce->usersDisliked);
        }
        return $listesSauce;
    }

    /**
     * @OA\Post(
     *     path="/api/sauces",
     *     tags={"Sauces"},
     *     security={{"bearerAuth":{}}},
     *     summary="Ajouter une nouvelle sauce",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                  @OA\Property( property="name", type="string", description="Nom de la sauce", example="Sauce piquante"),
     *                 @OA\Property( property="manufacturer", type="string", description="Nom du fabricant de la sauce", example="Piquante"),
     *                @OA\Property( property="description", type="string", description="Description de la sauce", example="Une sauce piquante"),
     *                @OA\Property( property="mainPepper", type="string", description="Principal ingrédient de la sauce", example="Piment"),
     *               @OA\Property( property="heat", type="integer", description="Force de la sauce sur une échelle de 1 à 10", example="10"),
     *              @OA\Property( property="image", type="string", description="Image de la sauce", example="https://www.sauce-piquante.fr/162-large_default/sauce-piquante-moruga-trinidad-scorpion.jpg"),
     *                required={"name", "manufacturer", "description", "mainPepper", "heat", "image"},
     *                 
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Retourne la sauce créée"),
     *     @OA\Response(response="400", description="Erreur de validation des données"),
     *     @OA\Response(response="401", description="Non autorisé")
     * )
     */
    public function create($request)
    {
        $user= $this->getUserFromToken();
        if($user == null){
            //On retourne une erreur 401 si l'utilisateur n'existe pas
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        //On verifie que les données envoyées sont valides
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'manufacturer' => 'required|string',
                'description' => 'required|string',
                'mainPepper' => 'required|string',
                'heat' => 'required|integer',
                'image' => 'required',
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 400);
        }
        //On enregistre l'image dans le dossier images
        if ($request->file('image') != null) {
            //On s'assure que l'image à une extension valide
            if (!preg_match('/^.*\.(?:png|jpg|jpeg|gif|svg)$/', $request->file('image')->getClientOriginalName())) {
                //on supprime l'image
                unlink($request->file('image')->getPathname());
                return response()->json(['error' => 'Invalid image'], 400);
            }
            $image= "storage/".$request->file('image')->store('/images', 'public');
        }
        else {
            if ($request->image)
                //On s'assure que l'image est bien une image avec une extension valide
                if (!preg_match('/^https?:\/\/.*\.(?:png|jpg|jpeg|gif|svg)$/', $request->image)) {
                    return response()->json(['error' => 'Invalid image'], 400);
                }
                $image = $request->image;

        }

        //On crée la sauce
        try {
            $sauce = Sauce::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'manufacturer' => $request->manufacturer,
                'description' => $request->description,
                'mainPepper' => $request->mainPepper,
                'heat' => $request->heat,
                'imageUrl' => $image,
                'likes' => 0,
                'dislikes' => 0,
                'usersLiked' => json_encode(["users" => []]),
                'usersDisliked' => json_encode(["users" => []]),
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 400);
        }


        //On retourne la sauce créée
        $sauce->usersLiked = json_decode($sauce->usersLiked);

        $sauce->usersDisliked = json_decode($sauce->usersDisliked);
        return $sauce;

    }


    public function store(Request $request)
    {
        return $this->create($request);
        
    }

    /**
     * @OA\Get(
     *     path="/api/sauces/{id}",
     *     summary="Obtenir une sauce spécifique",
     *     tags={"Sauces"},
     *    security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la sauce",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(response="200", description="Retourne la sauce spécifique"),
     *     @OA\Response(response="401", description="Non autorisé"),
     *     @OA\Response(response="404", description="Sauce non trouvée")
     * )
     */
    public function show($id)
    {
        //On récupère l'utilisateur à partir du token
        if($this->getUserFromToken() == null){
            //On retourne une erreur 401 si l'utilisateur n'existe pas
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        //On récupère la sauce
        $sauce = Sauce::find($id);
        //On vérifie que la sauce existe
        if ($sauce == null) {
            return response()->json(['error' => 'Sauce not found'], 404);
        }
        //On retourne la sauce
        $sauce->usersLiked = json_decode($sauce->usersLiked);
        $sauce->usersDisliked = json_decode($sauce->usersDisliked);
        return $sauce;
    }
    /**
     * @OA\PATCH(
     *     path="/api/sauces/{id}",
     *     tags={"Sauces"},
     *     summary="Modifier une sauce existante",
     *     description="Modifie une sauce existante dans la base de données tous les champs ne sont pas obligatoire",
     *     @OA\Parameter(
     *          name="id",
     *          description="ID de la sauce à modifier",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     description="Nom de la sauce",
     *                     example="Sauce piquante"
     *                 ),
     *                 @OA\Property(
     *                     property="manufacturer",
     *                     type="string",
     *                     description="Nom du fabricant de la sauce",
     *                     example="Fabricant de sauces inc."
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     description="Description de la sauce",
     *                     example="Une sauce piquante pour relever vos plats"
     *                 ),
     *                 @OA\Property(
     *                     property="mainPepper",
     *                     type="string",
     *                     description="Principal ingrédient de la sauce",
     *                     example="Piment"
     *                 ),
     *                 @OA\Property(
     *                     property="heat",
     *                     type="integer",
     *                     description="Force de la sauce sur une échelle de 1 à 10",
     *                     example=7
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     description="URL de l'image de la sauce",
     *                     example="https://www.sauce-piquante.fr/162-large_default/sauce-piquante-moruga-trinidad-scorpion.jpg"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="La sauce modifiée",
     *         @OA\JsonContent(ref="#/components/schemas/Sauce")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Le nom de la sauce est obligatoire"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Unauthorized"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sauce non trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Sauce not found"
     *             )
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function edit($id,$request)
    {
        //N'est jamais utilisé car le PATCH et appelle directement la méthode update 
        return response()->json(['error' => 'Not implemented'], 501);
    }
    /**
     * @OA\Put(
     *     path="/api/sauces/{id}",
     *     tags={"Sauces"},
     *     summary="Modifier une sauce existante",
     *     description="Modifie une sauce existante dans la base de données",
     *     @OA\Parameter(
     *          name="id",
     *          description="ID de la sauce à modifier",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     description="Nom de la sauce",
     *                     example="Sauce piquante"
     *                 ),
     *                 @OA\Property(
     *                     property="manufacturer",
     *                     type="string",
     *                     description="Nom du fabricant de la sauce",
     *                     example="Fabricant de sauces inc."
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     description="Description de la sauce",
     *                     example="Une sauce piquante pour relever vos plats"
     *                 ),
     *                 @OA\Property(
     *                     property="mainPepper",
     *                     type="string",
     *                     description="Principal ingrédient de la sauce",
     *                     example="Piment"
     *                 ),
     *                 @OA\Property(
     *                     property="heat",
     *                     type="integer",
     *                     description="Force de la sauce sur une échelle de 1 à 10",
     *                     example=7
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     description="URL de l'image de la sauce",
     *                     example="https://www.sauce-piquante.fr/162-large_default/sauce-piquante-moruga-trinidad-scorpion.jpg"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="La sauce modifiée",
     *         @OA\JsonContent(ref="#/components/schemas/Sauce")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Le nom de la sauce est obligatoire"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Unauthorized"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sauce non trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Sauce not found"
     *             )
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function update(Request $request, $id)
    {
        if($this->getUserFromToken() == null){
            //On retourne une erreur 401 si l'utilisateur n'existe pas
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        //On récupère la sauce
        $sauce = Sauce::find($id);
        //On vérifie que la sauce existe
        if ($sauce == null) {
            return response()->json(['error' => 'Sauce not found'], 404);
        }
        //On vérifie que l'utilisateur est bien le propriétaire de la sauce
        if ($sauce->user_id != $this->getUserFromToken()->id) {
            return response()->json(['error' => 'Unauthorized to edit this sauce'], 401);
        }

        //On verifie que les données envoyées sont valides
        try {
            //Si la methode est patch on ne vérifie pas les champs obligatoires
            if ($request->method() == 'PATCH') {
                $validatedData = $request->validate([
                    'name' => 'string',
                    'manufacturer' => 'string',
                    'description' => 'string',
                    'mainPepper' => 'string',
                    'heat' => 'integer|min:0|max:10',
                ]);
            }
            else {
                $validatedData = $request->validate([
                    'name' => 'required|string',
                    'manufacturer' => 'required|string',
                    'description' => 'required|string',
                    'mainPepper' => 'required|string',
                    'heat' => 'required|integer|min:0|max:10',
                ]);
            }

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 400);
        }

        //On update toutes les nouvelles données de la sauce
        foreach (['name', 'manufacturer', 'description', 'mainPepper', 'heat'] as $attribute) {
            if ($request->has($attribute)) {
                $sauce->$attribute = $request->$attribute;
            }
        }
        if ($request->image){
            //Bizarement là on ne peut pas envoyer une image mais seulement des url (Les files n'apparaissent pas dans la requête)
            if (!preg_match('/^https?:\/\/.*\.(?:png|jpg|jpeg|gif|svg)$/', $request->image)) {
                return response()->json(['error' => 'Invalid image'], 400);
            }
            $sauce->imageUrl = $request->image;
        }
        //On sauvegarde la sauce
        $sauce->save();
        //On retourne la sauce
        $sauce->usersLiked = json_decode($sauce->usersLiked);
        $sauce->usersDisliked = json_decode($sauce->usersDisliked);
        return $sauce;



    }

    /**
     * @OA\Delete(
     *     path="/api/sauces/{id}",
     *     summary="Supprimer une sauce",
     *     description="Supprimer une sauce existante.",
     *     operationId="deleteSauce",
     *     tags={"Sauces"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la sauce à supprimer.",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sauce supprimée avec succès."
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé à supprimer cette sauce."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sauce non trouvée."
     *     )
     * )
     */
    public function destroy($id)
    {
        if($this->getUserFromToken() == null){
            //On retourne une erreur 401 si l'utilisateur n'existe pas
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        //On récupère la sauce
        $sauce = Sauce::find($id);
        //On vérifie que la sauce existe
        if ($sauce == null) {
            return response()->json(['error' => 'Sauce not found'], 404);
        }

        //On vérifie que l'utilisateur est bien le propriétaire de la sauce
        if ($sauce->user_id != $this->getUserFromToken()->id) {
            return response()->json(['error' => 'Unauthorized to edit this sauce'], 401);
        }

        //On supprime l'image de la sauce
        if ($sauce->imageUrl != null) {
            $path = str_replace("storage/", "", $sauce->imageUrl);
            //on regarde si le fichier existe
            if (Storage::disk('public')->exists($path)){
                //on le supprime
                Storage::disk('public')->delete($path);
            }
        }

        //On supprime la sauce
        $sauce->delete();
        return response()->json(['message' => 'Sauce deleted'], 200);
        
    }


    /**
     * @OA\Post(
     *     path="/api/sauces/{id}/react",
     *     tags={"Sauces"},
     *     security={{"bearerAuth":{}}},
     *     summary="Réagir à une sauce",
     *     description="Permet de liker ou disliker, ou de retirer son like ou dislike d'une sauce.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la sauce",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="reaction", type="string", enum={"like", "dislike", "unlike", "undislike"}),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Retourne la sauce mise à jour"),
     *     @OA\Response(response="400", description="Erreur de validation des données"),
     *     @OA\Response(response="401", description="Non autorisé"),
     *     @OA\Response(response="404", description="Sauce non trouvée")
     * )
     */
    public function reactToSauce(Request $request, $id){
        if($this->getUserFromToken() == null){
            //On retourne une erreur 401 si l'utilisateur n'existe pas
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user=$this->getUserFromToken()->id;

        //On récupère la sauce
        $sauce = Sauce::find($id);
        //On vérifie que la sauce existe
        if ($sauce == null) {
            return response()->json(['error' => 'Sauce not found'], 404);
        }
        
        //Les 4 cas possibles : like, dislike, unlike, undislike
        //Mais on doit vériier que l'utilisateur ne peut pas like et dislike en même temps
        //On doit aussi vérifier que l'utilisateur ne peut pas unlike ou undislike une sauce qu'il n'a pas liké ou disliké
        $etatActuel="";
        $usersDisliked = json_decode($sauce->usersDisliked)->users;
        $usersLiked = json_decode($sauce->usersLiked)->users;
        if (in_array($user, $usersDisliked)) {
            $etatActuel = "dislike";
        }
        if (in_array($user, $usersLiked)) {
            $etatActuel = "like";
        }


        
        //Les 4 cas possibles : like, dislike, unlike, undislike
        switch ($request->reaction) {
            case "like":
                if ($etatActuel == "like") {
                    return response()->json(['error' => 'Already liked'], 400);
                }
                if ($etatActuel == "dislike") {
                    $usersDisliked = array_diff($usersDisliked, [$user]);
                    $sauce->dislikes--;
                }
                $usersLiked[] = $user;
                $sauce->likes++;
                break;
            case "dislike":
                if ($etatActuel == "dislike") {
                    return response()->json(['error' => 'Already disliked'], 400);
                }
                if ($etatActuel == "like") {
                    $usersLiked = array_diff($usersLiked, [$user]);
                    $sauce->likes--;
                }
                $usersDisliked[] = $user;
                $sauce->dislikes++;
                break;

            case "unlike":
                if ($etatActuel != "like") {
                    return response()->json(['error' => 'Not liked'], 400);
                }
                $usersLiked = array_diff($usersLiked, [$user]);
                $sauce->likes--;
                break;

            case "undislike":
                if ($etatActuel != "dislike") {
                    return response()->json(['error' => 'Not disliked'], 400);
                }
                $usersDisliked = array_diff($usersDisliked, [$user]);
                $sauce->dislikes--;
                break;

            default:
                return response()->json(['error' => 'Invalid reaction'], 400);
                break;
            }
        
        $sauce->usersLiked = json_encode(['users' => $usersLiked]);
        $sauce->usersDisliked = json_encode(['users' => $usersDisliked]);
        $sauce->save();
        return response()->json(["success" => "switched to ".$request->reaction, "sauce" => $sauce], 200);
    }
}
