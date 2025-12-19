<?php 
require 'config.php'; 
$id = intval($_GET['id']);
$res = mysqli_query($conn, "SELECT * FROM candidats WHERE id = $id");
$c = mysqli_fetch_assoc($res);

if (!$c) { die("Candidat introuvable"); }

$color = ($c['score'] >= 75) ? '#10b981' : (($c['score'] >= 50) ? '#f59e0b' : '#ef4444');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport ATS - <?php echo htmlspecialchars($c['nom_candidat']); ?></title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; padding: 50px; }
        .fiche { background: white; max-width: 800px; margin: auto; padding: 40px; border-radius: 15px; border-top: 10px solid <?php echo $color; ?>; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
        .score-box { float: right; font-size: 48px; font-weight: bold; color: <?php echo $color; ?>; }
        .info-label { font-weight: bold; color: #64748b; text-transform: uppercase; font-size: 12px; }
        .info-value { font-size: 18px; margin-bottom: 20px; }
        .skills-found { background: #f1f5f9; padding: 20px; border-radius: 8px; font-style: italic; }
        
        /* Style pour le bouton Imprimer */
        .btn-print { background: #1e293b; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; float: right; margin-top: 20px; }
        
        /* Cache les boutons lors de l'impression PDF */
        @media print {
            .btn-print, .back-link { display: none; }
            body { background: white; padding: 0; }
            .fiche { box-shadow: none; border: 1px solid #ddd; }
        }
    </style>
</head>
<body>
    <div class="fiche">
        <div class="score-box"><?php echo $c['score']; ?>%</div>
        <h1>Rapport d'Analyse</h1>
        
        <p class="info-label">Candidat</p>
        <p class="info-value"><?php echo htmlspecialchars($c['nom_candidat']); ?></p>

        <p class="info-label">Poste Visé</p>
        <p class="info-value"><?php echo htmlspecialchars($c['nom_poste']); ?></p>

        <p class="info-label">Localisation & Diplôme</p>
        <p class="info-value"><?php echo htmlspecialchars($c['ville']); ?> | <?php echo htmlspecialchars($c['niveau_etudes']); ?></p>

        <p class="info-label">Compétences Détectées</p>
        <div class="skills-found"><?php echo htmlspecialchars($c['competences_trouvees']); ?></div>

        <button class="btn-print" onclick="window.print()">Exporter en PDF</button>
        <a href="liste.php" class="back-link" style="display:inline-block; margin-top:25px; color:#64748b;">← Retour à la liste</a>
    </div>
</body>
</html>