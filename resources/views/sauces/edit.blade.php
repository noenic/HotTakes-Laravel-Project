@extends('layouts.app')
@section('content')

<div class="container">
    <!-- On fait un formulaire de modification e la sauce  -->
    <h1 class="text-center ">Modifier une sauce</h1>
    <div class="text-center  d-flex justify-content-center">
                    <img id="img-preview" src="{{ asset($sauce->imageUrl) }}" alt="Image de la sauce" class="img-fluid my-3" height="150px" width="150px">
        </div>
        <form action="{{ route('sauces.update', $sauce->id) }}" method="POST" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label for="name">Nom</label>
                <input type="text" class="form-control" name="name" id="name" placeholder="Nom" required value="{{ $sauce->name }}">
            </div>
            <div class="form-group">
                <label for="manufacturer">Fabricant</label>
                <input type="text" class="form-control" name="manufacturer" id="manufacturer" placeholder="Fabricant" required value="{{ $sauce->manufacturer }}">
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" name="description" id="description" rows="3" required>{{ $sauce->description }}</textarea>
            </div>
            <div class="form-group">
                <label for="mainPepper">Piment principal</label>
                <input type="text" class="form-control" name="mainPepper" id="mainPepper" placeholder="Piment principal" required value="{{ $sauce->mainPepper }}">
            </div>
            <!-- On fait un slider pour le heat avec un input range et un input number a coté du slider -->
            <div class="form-group">
                <label for="heat">Chaleur</label>
                <div class="slidecontainer d-flex">
                    <input type="range" min="1" max="10"  class="slider" id="heat" name="heat" required value = "{{ $sauce->heat }}">
                    <div style="visibility:hidden"> -> </div>
                    <input type="number" min="1" max="10" id="heatnum" name="heatnum" width="10px">
                </div>
            </div>
            <!-- input file de l'image -->
            <div class="form-group">
                <label for="imageUrl">Image</label>
                <input id="img-input" type="text" class="form-control" name="image" id="imageUrl" placeholder="URL de Image" required value="{{ $sauce->imageUrl }}">
                <small id="imgHelp" class="form-text text-muted">Vous pouvez aussi mettre une URL d'image en faisant un clique droit sur l'input</small>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary text-center my-5">Modifier</button>
            </div>
        </form>

    </div>

<script>
//Si on fait un clique droit sur l'input
document.getElementById("img-input").addEventListener("contextmenu", function(e){
    e.preventDefault(); //On empêche l'affichage du menu contextuel
    if (this.type == 'text') //Si l'input est un input text
        this.type = 'file'; //On le transforme en un input file
    else{ //Sinon (c'est à dire si c'est un input file
        this.type = 'text'; //On le transforme en un input text
    }
});

//On récupère le slider
var slider = document.getElementById("heat");
//On récupère l'input number
var output = document.getElementById("heatnum");
//On met la valeur du slider dans l'input number
output.value = slider.value;
//On met à jour l'input number quand on bouge le slider
slider.oninput = function() {
    output.value = this.value;
}
//On met à jour le slider quand on change la valeur de l'input number
output.oninput = function() {
    slider.value = this.value;
}

//Si il y a quelque chose dans l'input file on affiche l'image
document.getElementById("img-input").addEventListener("change", function(e){
    if (this.value != ''){ //Si il y a quelque chose dans l'input file
        if(this.files && this.files[0]){
            document.getElementById("img-preview").src = URL.createObjectURL(this.files[0]); //On met l'image dans l'input file dans l'image
        }
        else{
            // Si l'input file n'est pas un fichier (c'est à dire si c'est une URL)
            //On vérifie que l'URL est bien une image
            if (this.value.match(/\.(jpeg|jpg|gif|png)$/) != null){
                document.getElementById("img-preview").src = this.value; //On met l'URL dans l'image
            }
            else{
                return;
            }
        }
        document.getElementById("img-preview").style.display = "block"; //On affiche l'image

    }
    else{ //Sinon
        document.getElementById("img-preview").style.display = "none"; //On cache l'image
    }
});

</script>
@endsection
