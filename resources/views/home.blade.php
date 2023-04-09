@extends('layouts.app')

@section('content')
<div class="container-home">
		<h1>Le classement des sauces piquantes</h1>
			<section>
				<h2>Bienvenue sur notre site</h2>
				<p>Vous êtes fan de sauce piquante ? Vous êtes au bon endroit ! Notre site regroupe les meilleures sauces piquantes du monde entier, notées et classées par les utilisateurs. Vous pourrez ainsi découvrir de nouvelles sauces, partager votre passion avec d'autres fans et peut-être même trouver votre nouvelle sauce préférée !</p>
				<p>Voici quelques fun facts sur les piments :</p>
				<ul>
					<li>Le piment le plus fort du monde est le Carolina Reaper, qui atteint jusqu'à 2,2 millions d'unités sur l'échelle de Scoville</li>
					<li>Les piments ont été cultivés pour la première fois en Amérique du Sud il y a plus de 7000 ans</li>
					<li>Les piments ne sont pas toujours rouges, ils peuvent être de toutes les couleurs : vert, jaune, orange, marron...</li>
					<li>La capsaïcine, la molécule responsable de la sensation de brûlure dans les piments, est utilisée dans certains médicaments contre la douleur</li>
				</ul>
			</section>

			<section class="d-flex justify-content-center">
				<a href="{{ route('sauces.index') }}" class="btn btn-danger">Voir les sauces</a>

			</section>
		<footer>
			<p>&copy; 2023 - Le classement des sauces piquantes</p>
		</footer>
	</div>
@endsection
<style>

		.container-home {
			margin-top: 2rem;
			margin-bottom: 2rem;
			padding: 2rem;
			background-color: #fff;
			border-radius: 10px;
			box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
			margin-left: 5%;
			margin-right: 5%;
		}
		h1, h2 {
			color: #dc3545;
			font-weight: bold;
			text-align: center;
		}
		section {
			margin-top: 2rem;
			margin-bottom: 2rem;
			text-align: justify;
		}
		section ul {
			margin-top: 1rem;
			margin-left: 2rem;
			padding-left: 1rem;
			border-left: 3px solid #dc3545;
			list-style-type: none;
		}
		footer {
			margin-top: 2rem;
			text-align: center;
		}
	</style>
