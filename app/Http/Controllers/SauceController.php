<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sauce;
use Illuminate\Support\Facades\Storage;


class SauceController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    //
    public function index()
    {
        $sauces = Sauce::all();
        $sauces = Sauce::paginate(5);
        return view('sauces.index', compact('sauces'));
    }


    public function create()
    {
        return view('sauces.create');
    }


    public function store(Request $request)
    {
        //Image est soit une image soit une url
        $request->validate([
            'name' => 'required',
            'manufacturer' => 'required',
            'description' => 'required',
            'mainPepper' => 'required',
            'heat' => 'required|min:1|max:10',
            'image' => 'required',
        ]);
        $sauce = new Sauce();
        $sauce->user_id = auth()->user()->id;
        $sauce->name = $request->name;
        $sauce->manufacturer = $request->manufacturer;
        $sauce->description = $request->description;
        $sauce->mainPepper = $request->mainPepper;
        $sauce->heat = $request->heat;
        $sauce->likes = 0;
        $sauce->dislikes = 0;
        $sauce->usersLiked = json_encode(["users" => []]);
        $sauce->usersDisliked = json_encode(["users" => []]);
        // Image est un champ de type file
        if ($request->file('image') != null) {
            $sauce->imageUrl = "storage/".$request->file('image')->store('/images', 'public');
        }
        else {
            if ($request->image)
                $sauce->imageUrl = $request->image;
        }
        $sauce->save();
        return redirect()->route('sauces.index');
    }

    public function show($id)
    {
        $sauce = Sauce::find($id);
        if($sauce == null)
            return redirect()->route('sauces.index');
        return view('sauces.show', compact('sauce'));
    }

    public function edit($id)
    {
        $sauce = Sauce::find($id);
        if($sauce == null || $sauce->user_id != auth()->user()->id)
            return redirect()->route('sauces.index');
        return view('sauces.edit', compact('sauce'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'manufacturer' => 'required',
            'description' => 'required',
            'mainPepper' => 'required',
            'heat' => 'required',
            'image' => 'required',
        ]);
        $sauce = Sauce::find($id);
        if($sauce == null || $sauce->user_id != auth()->user()->id){
            return redirect()->route('sauces.index');
        }
        $sauce->name = $request->name;
        $sauce->manufacturer = $request->manufacturer;
        $sauce->description = $request->description;
        $sauce->mainPepper = $request->mainPepper;
        $sauce->heat = $request->heat;
        // Image est un champ de type file
        if ($request->file('image') != null) {
            
            //On supprime l'ancienne image
            if ($sauce->imageUrl != null) {
                $path = str_replace("storage/", "", $sauce->imageUrl);
                //on regarde si le fichier existe
                if (Storage::disk('public')->exists($path)){
                    //on le supprime
                    Storage::disk('public')->delete($path);
                }
            }
            $sauce->imageUrl = "storage/".$request->file('image')->store('/images', 'public');
        }
        else {
            if ($request->image)
                $sauce->imageUrl = $request->image;
        }
        $sauce->save();
        return redirect()->route('sauces.show', $id);
    }

    public function destroy($id)
    {
        $sauce = Sauce::find($id);
        if($sauce == null || $sauce->user_id != auth()->user()->id)
            return redirect()->route('sauces.index');
        
        //On supprime l'image
        if ($sauce->imageUrl != null) {
            $path = str_replace("storage/", "", $sauce->imageUrl);
            //on regarde si le fichier existe
            if (Storage::disk('public')->exists($path)){
                //on le supprime
                Storage::disk('public')->delete($path);
            }
        }
        $sauce->delete();
        return redirect()->route('sauces.index');
    }

    public function react($id)
    {
        $reaction = request()->input('reaction');
        $sauce = Sauce::find($id);
        if ($sauce == null) {
            return redirect()->route('sauces.show', $id); 
        }
        $user = auth()->user()->id;
        //Les 4 cas possibles : like, dislike, unlike, undislike
        //Mais on doit vériier que l'utilisateur ne peut pas like et dislike en même temps
        //On doit aussi vérifier que l'utilisateur ne peut pas unlike ou undislike une sauce qu'il n'a pas liké ou disliké
        $etatActuel="";
        if(in_array($user, json_decode($sauce->usersLiked)->users)) {
            $etatActuel = "like";
        }
        else if(in_array($user, json_decode($sauce->usersDisliked)->users)) {
            $etatActuel = "dislike";
        }
        $usersDisliked = json_decode($sauce->usersDisliked)->users;
        $usersLiked = json_decode($sauce->usersLiked)->users;

        //Cas 1 : like
        if($reaction == "like") {
            if($etatActuel == "dislike") {
                //On enlève le dislike
                $sauce->dislikes = $sauce->dislikes - 1;
                $usersDisliked = array_diff($usersDisliked, [$user]);

                //On ajoute le like
                $sauce->likes = $sauce->likes + 1;
                array_push($usersLiked, $user);
                
 
            }
            else {
                //On ajoute le like
                $sauce->likes = $sauce->likes + 1;
                array_push($usersLiked, $user);
            }
        }
        //Cas 2 : dislike
        else if($reaction == "dislike") {
            if($etatActuel == "like") {
                //On enlève le like
                $sauce->likes = $sauce->likes - 1;
                $usersLiked = array_diff($usersLiked, [$user]);

                //On ajoute le dislike
                $sauce->dislikes = $sauce->dislikes + 1;
                array_push($usersDisliked, $user);
            }
            else {
                //On ajoute le dislike
                $sauce->dislikes = $sauce->dislikes + 1;
                array_push($usersDisliked, $user);
            }
        }

        //Cas 3 : unlike
        else if($reaction == "unlike") {
            if($etatActuel == "like") {
                //On enlève le like
                $sauce->likes = $sauce->likes - 1;
                $usersLiked = array_diff($usersLiked, [$user]);
            }
        }

        //Cas 4 : undislike
        else if($reaction == "undislike") {
            if($etatActuel == "dislike") {
                //On enlève le dislike
                $sauce->dislikes = $sauce->dislikes - 1;
                $usersDisliked = array_diff($usersDisliked, [$user]);
            }
        }


        $sauce->usersLiked = json_encode(["users" => $usersLiked]);
        $sauce->usersDisliked = json_encode(["users" => $usersDisliked]);
        


        $sauce->save();
        return redirect()->route('sauces.show', $id);

    }


        


}




