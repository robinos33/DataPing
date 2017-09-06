<?php

?>
<div class="wrap">
	<h1 class="DataPing_title">Les équipes </h1>

	<p>Insérez le shortcode dans la page ou l'article où vous désirez afficher la poule de chaque équipe</p>

	<form class="DataPing_liste_admin">
		<table class="wp-list-table widefat fixed striped posts">
			<thead>
			<tr>
				<td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text"
				                                                                for="cb-select-all-1">Select All</label><input
						id="cb-select-all-1" type="checkbox"></td>
				<th>Equipe</th>
				<th>Division</th>
				<th>Shortcode</th>
			</tr>
			</thead>
			<tbody id="the-list">
			<?php
			$equipes      = new Equipes();
			$listeEquipes = $equipes->getEquipes( 'MF' );
			foreach ( $listeEquipes as $equipe ) {
				/* @var Equipe $equipe */
				?>
				<tr>
					<th scope="row" class="check-column"><input type="checkbox"/></th>
					<td><?php echo $equipe->getLibequipe(); ?></td>
					<td><?php echo $equipe->getLibdivision(); ?></td>
					<td><?php echo '[equipe iddiv="' . $equipe->getIddiv() . '" idpoule="' . $equipe->getIdpoule() . '"]'; ?></td>
				</tr>
				<?php
			}
			?>
			</tbody>
		</table>
	</form>
</div>
