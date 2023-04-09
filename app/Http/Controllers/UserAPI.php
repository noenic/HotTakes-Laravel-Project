<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
/**
 * @OA\Info(
 *      version="1.2.0",
 *      title="APIREST  HotSauce",
 *      description="Une API REST pour le site HotSauce",
 *      @OA\Contact(
 *          email="moi@contact.com"
 *      )
 * )
 * @OA\Server(
 *     url="http://localhost:8000",
 *    description="API REST"
 * )
 * @OA\Tag(
 *    name="User",
 *   description="Opérations sur les utilisateurs"
 * )
 * @OA\Tag(
 *   name="Sauces",
 *  description="Opérations sur les sauces"
 * )
 * 
 *
 * 
 * @OA\SecurityScheme(
 *     type="oauth2",
 *     securityScheme="bearerAuth",
 *     in="header",
 *     description="OAuth2 Authentication",
 *     @OA\Flow(
 *         flow="password",
 *         tokenUrl="/api/auth/login",
 *         scopes={}
 *     )
 * )
 * 
 * @OA\Schema(
 *    schema="User",
 *   type="object",
 *  @OA\Property(property="id", type="integer"),
 * @OA\Property(property="email", type="string"),
 * @OA\Property(property="password", type="string"),
 * )
 * 
 * 
 */

class UserAPI extends Controller
{
        /**
     * @OA\Post(
     *      path="/api/auth/signup",
     *      summary="Créer un nouvel utilisateur",
     *      tags={"User"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"email","password"},
     *              @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *              @OA\Property(property="password", type="string", minLength=8, example="password123")
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Utilisateur créé",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="string")
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Requête invalide",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string")
     *          )
     *      )
     * )
     */
    public function signup(Request $request)
        {
            // Validez les données de la requête
            try {
                $validatedData = $request->validate([
                    'email' => 'required|email|unique:users',
                    'password' => 'required|min:8',
                ]);
            } catch (\Throwable $th) {

                return response()->json(['error' => $th->getMessage()], 400);
            }
            

            // Créer un nouvel utilisateur et retourne success si tout est ok
            try {
                $user = User::create([
                    'email' => $validatedData['email'],
                    'password' => bcrypt($validatedData['password']),
                ]);
            } catch (\Throwable $th) {
                return response()->json(['error' => $th->getMessage()], 400);
            }
            
            return response()->json(['success' => 'User created'], 200);
        }
           /**
         * @OA\Post(
         *      path="/api/auth/login",
         *      summary="Connecter un utilisateur",
         *      tags={"User"},
         *      @OA\RequestBody(
         *          required=true,
         *          @OA\JsonContent(
         *              required={"email","password"},
         *              @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
         *              @OA\Property(property="password", type="string", example="password123")
         *          )
         *      ),
         *      @OA\Response(
         *          response="200",
         *          description="Token d'authentification",
         *          @OA\JsonContent(
         *              @OA\Property(property="token", type="string")
         *          )
         *      ),
         *      @OA\Response(
         *          response="400",
         *          description="Requête invalide",
         *          @OA\JsonContent(
         *              @OA\Property(property="error", type="string")
         *          )
         *      ),
         *      @OA\Response(
         *          response="401",
         *          description="Informations d'identification invalides",
         *          @OA\JsonContent(
         *              @OA\Property(property="error", type="string")
         *          )
         *      ),
         * )
         */
        public function login(Request $request)
        {
            // Valide les données de la requête
            //Si on recoit "username" au lieu de "email" on le remplace par "email" (c'est swagger qui veut pas prendre "email")
            if ($request->has('username')) {
                $request->merge(['email' => $request->username]);
            }


            try {
                $validatedData = $request->validate([
                    'email' => 'required|email',
                    'password' => 'required',
                ]);
            } catch (\Throwable $th) {

                return response()->json(['error' => $th->getMessage()], 400);
            }

            // Vérifiez les informations d'identification
            if (!Auth::attempt($validatedData)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            // Supprimer les remplace jetons d'API de l'utilisateur et en créer un nouveau qui sera valide 3 minutes
            Auth::user()->tokens()->delete();
            $token = Auth::user()->createToken('authToken')->plainTextToken;



            // Retourner le token d'API
            return response()->json(['access_token' => $token], 201);
        }
        /**
         * @OA\Delete(
         *      path="/api/auth/logout",
         *      security={{"bearerAuth":{}}},
         *      summary="Déconnexion de l'utilisateur",
         *      description="Supprime le jeton d'API de l'utilisateur connecté.",
         *      tags={"User"},
         *      @OA\Response(
         *          response=200,
         *          description="Success: Jeton d'API supprimé",
         *          @OA\JsonContent(
         *              @OA\Property(property="success", type="string", example="Déconnexion réussie")
         *          )
         *      ),
         *      @OA\Response(
         *          response=401,
         *          description="Unauthorized: Token d'API manquant ou invalide",
         *          @OA\JsonContent(
         *              @OA\Property(property="error", type="string", example="Non autorisé")
         *          )
         *      ),
         *      @OA\Response(
         *          response=500,
         *          description="Internal Server Error: Erreur lors de la suppression du token d'API",
         *          @OA\JsonContent(
         *              @OA\Property(property="error", type="string", example="Erreur interne du serveur")
         *          )
         *      )
         * )
         */
        public function logout()
        {
            //On recupere le token dans le header
            $token = request()->bearerToken();
            try{

                $user =PersonalAccessToken::findToken($token)->tokenable;
                //On supprime le token
                $user->tokens()->delete();
                return response()->json(['success' => 'success logout'], 200);
            }
            catch(\Throwable $th){
                return response()->json(['error' => 'Unhautorized'], 401);
        }
    }

        

    }


?>