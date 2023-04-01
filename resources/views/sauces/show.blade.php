@extends('layouts.app')
@section('content')
<head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>


<?php

// On regarde si l'utilisateur a liker ou disliker la sauce
$like = false;
$dislike = false;
if (in_array(Auth::user()->id, json_decode($sauce->usersLiked)->users)) {
    $like = true;
}

if (in_array(Auth::user()->id, json_decode($sauce->usersDisliked)->users)) {
    $dislike = true;
}

// On regarde si l'utilisateur est le créateur de la sauce
$creator= Auth::user()->id == $sauce->user_id;

//On recupere le créateur de la sauce , on coupe au @ et on affiche uniquement le nom
$creatorOfSauce = App\Models\User::find($sauce->user_id)->email;
$creatorOfSauce = explode("@", $creatorOfSauce)[0];





?>
<!-- On affiche la sauce -->
<div class="container">
    <h1 class="text-center">{{ $sauce->name }}</h1>
    <!-- On affiche en petit l'email du createur -->
    <p class="text-center">Suggéré par {{ $creatorOfSauce }}</p>
    @if ($creator)
    <div class="d-flex justify-content-center mb-3">
        <a href="{{ route('sauces.edit', $sauce->id) }}" class="btn btn-primary mx-1">Modifier</a>
        <form action="{{ route('sauces.destroy', $sauce->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger mx-1">Supprimer</button>
        </form>
    </div>
    @endif
    <div class="d-flex justify-content-center mb-3">
        <div class="sauce-container d-flex flex-column align-items-center">
            <div class="sauce-image">
                <img src="{{ asset($sauce->imageUrl) }}" alt="{{ $sauce->name }}" height="194px" width="194px">
            </div>
            <div class="sauce-info" style="text-align: center;">
                <label for="fabricant">fabricant</label>
                <p name="fabricant">{{ $sauce->manufacturer }}</p>
                <label for="heat">Heat</label>
                <p name="heat">{{ $sauce->heat }}/10</p>
                <label for="description">Description</label>
                <p name="description">{{ $sauce->description }}</p>
                <label for="mainPepper">Piment principal</label>
                <p name="mainPepper">{{ $sauce->mainPepper }}</p>
            </div>
            <!-- On fait une section avec des bouton like dislike, si l'utilisateur a liker la sauce alors 
            on chaneg el bouton en ne plus liker -->
            <!-- Pour reagir on utilsie la route sauces.react avec le parametre id de la sauce et la reaction qui peut etre like ou dislike par le POST de tag reaction-->
            <!-- On affiche juste les bouton -->
            <div class="d-flex justify-content-center">
                <!-- HTML pour le bouton de like -->
                <form action="{{ route('sauces.react', ['id' => $sauce->id]) }}" method="POST">
                    @csrf
                @if (!$like)
                    <input type="hidden" name="reaction" value="like">
                    <button type="submit" class="like">
                    <p>{{ $sauce->likes}}</p>
                    <span>Like</span>
                    </button>
                @else
                    <input type="hidden" name="reaction" value="unlike">
                    <button type="submit" class="like">
                    <p>{{ $sauce->likes}}</p>
                    <span>Ne plus liker</span>
                    </button>
                @endif
                </form>
                
                <!-- HTML pour le bouton de dislike -->
                <form action="{{ route('sauces.react', ['id' => $sauce->id]) }}" method="POST">
                    @csrf
                @if (!$dislike)
                    <input type="hidden" name="reaction" value="dislike">
                    <button type="submit" class="dislike">
                    <p>{{ $sauce->dislikes}}</p>
                    <span>Dislike</span>
                    </button>
                @else
                    <input type="hidden" name="reaction" value="undislike">
                    <button type="submit" class="dislike">
                    <p>{{ $sauce->dislikes}}</p>
                    <span>Ne plus disliker</span>
                    </button>
                @endif
                </form>
                </div>
        


    </div>
</div>



<style>
/* CSS pour le conteneur de like/dislike */
.d-flex {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 20px 0;
}

/* CSS pour les boutons de like et dislike */
.like, .dislike {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 0 20px;
    cursor: pointer;
    border : none;
    height : 120px;
    width : 120px;
    border-radius: 10%;
    background-color : transparent;

}

/* CSS pour l'icône de like */
.like:before {
    content: "\f164";
    font-family: "Font Awesome 5 Free";
    font-size: 24px;
    color: #0d6efd;
    @if ($like)
        font-weight: 600;
    @endif
    
}

/* CSS pour l'icône de dislike */
.dislike:before {
    content: "\f164";
    font-family: "Font Awesome 5 Free";
    font-size: 24px;
    color: #dc3545;
    transform: rotate(180deg);
    @if ($dislike)
        font-weight: 600;
    @endif
}

/* CSS pour le nombre de like/dislike */
p:first-child {
    font-size: 24px;
    margin: 5px 0;
}

/* CSS pour le texte "Like/Dislike" */
p:last-child {
    font-size: 16px;
    margin: 0;
}

/* CSS pour l'effet de survol */
.like:hover, .dislike:hover {
    transform: scale(1.1);
    filter: brightness(0.8);
}



</style>


@endsection