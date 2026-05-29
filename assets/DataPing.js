jQuery(document).ready(function ($) {

    // ===== Tableau de joueurs triable =====
    jQuery('.sortableTable').tablesorter();

    // ===== Feuilles de match =====

    // Clic sur une ligne de rencontre expandable
    $(document).on('click', '.dataping-expandable', function () {
        var $row       = $(this);
        var $detailRow = $row.next('.dataping-feuille-row');
        var $icon      = $row.find('.dataping-expand-icon');
        var $content   = $detailRow.find('.dataping-feuille-content');

        if ($detailRow.is(':visible')) {
            // Réduire
            $detailRow.slideUp(200);
            $icon.text('▶');
            return;
        }

        // Développer
        $detailRow.slideDown(200);
        $icon.text('▼');

        // Déjà chargé ?
        if ($content.hasClass('dataping-loaded')) {
            return;
        }

        // Indiquer le chargement
        $content.html('<p class="dataping-feuille-loading">Chargement de la feuille de match…</p>');

        $.ajax({
            url:  DataPingAjax.ajaxurl,
            type: 'POST',
            data: {
                action:     'dataping_feuille_match',
                renc_id:    $row.data('renc-id'),
                is_retour:  $row.data('is-retour')
            },
            success: function (response) {
                if (response.success) {
                    $content.html(buildFeuilleHtml(response.data));
                    $content.addClass('dataping-loaded');
                } else {
                    $content.html('<p class="dataping-feuille-error">Feuille de match non disponible.</p>');
                }
            },
            error: function () {
                $content.html('<p class="dataping-feuille-error">Erreur de chargement.</p>');
            }
        });
    });

    /**
     * Construit le HTML de la feuille de match à partir des données AJAX.
     * @param {Object} data  { resultat, joueur, partie }
     * @returns {string}
     */
    function buildFeuilleHtml(data) {
        var html = '<div class="dataping-feuille">';

        // --- Score global ---
        if (data.resultat) {
            var r    = data.resultat;
            var resA = parseInt(r.resa, 10);
            var resB = parseInt(r.resb, 10);
            html += '<div class="dataping-feuille-resultat">';
            html += '<span class="dataping-feuille-equipe' + (resA > resB ? ' dataping-winner' : '') + '">' + esc(r.equa) + '</span>';
            html += '<span class="dataping-feuille-score"> ' + esc(r.resa) + ' – ' + esc(r.resb) + ' </span>';
            html += '<span class="dataping-feuille-equipe' + (resB > resA ? ' dataping-winner' : '') + '">' + esc(r.equb) + '</span>';
            html += '</div>';
        }

        // --- Composition ---
        if (data.joueur) {
            var joueurs = Array.isArray(data.joueur) ? data.joueur : [data.joueur];
            if (joueurs.length > 0) {
                html += '<h6 class="dataping-feuille-section">Composition</h6>';
                html += '<table class="dataping-table dataping-feuille-compo"><thead><tr>';
                html += '<th>Équipe A</th><th>Classement</th><th>Équipe B</th><th>Classement</th>';
                html += '</tr></thead><tbody>';
                joueurs.forEach(function (j) {
                    html += '<tr>';
                    html += '<td>' + esc(j.xja || '') + '</td>';
                    html += '<td class="center">' + esc(j.xca || '') + '</td>';
                    html += '<td>' + esc(j.xjb || '') + '</td>';
                    html += '<td class="center">' + esc(j.xcb || '') + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table>';
            }
        }

        // --- Parties ---
        if (data.partie) {
            var parties = Array.isArray(data.partie) ? data.partie : [data.partie];
            if (parties.length > 0) {
                html += '<h6 class="dataping-feuille-section">Résultats des parties</h6>';
                html += '<table class="dataping-table dataping-feuille-parties"><thead><tr>';
                html += '<th class="left">Joueur A</th><th>Sc.</th><th></th><th>Sc.</th><th class="left">Joueur B</th><th>Détail</th>';
                html += '</tr></thead><tbody>';
                parties.forEach(function (p) {
                    var wonA = String(p.scorea) === '1';
                    var wonB = String(p.scoreb) === '1';
                    html += '<tr>';
                    html += '<td class="' + (wonA ? 'dataping-winner' : '') + '">' + esc(p.ja  || '') + '</td>';
                    html += '<td class="center dataping-score">' + esc(String(p.scorea)) + '</td>';
                    html += '<td class="center dataping-tiret">–</td>';
                    html += '<td class="center dataping-score">' + esc(String(p.scoreb)) + '</td>';
                    html += '<td class="' + (wonB ? 'dataping-winner' : '') + '">' + esc(p.jb  || '') + '</td>';
                    html += '<td class="dataping-sets">'  + esc(p.detail || '') + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table>';
            }
        }

        html += '</div>';
        return html;
    }

    /** Échappe les caractères HTML spéciaux. */
    function esc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

});
