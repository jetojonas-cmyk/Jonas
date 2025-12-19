<?php 
session_start(); 
if (!isset($_SESSION['logged_in'])) { header("Location: login.php"); exit(); }
require 'config.php'; 

// --- CALCUL DES STATISTIQUES ---
// 1. Total
$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as nb FROM candidats"))['nb'] ?? 0;

// 2. Élite (Score >= 75)
$elite = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as nb FROM candidats WHERE score >= 75"))['nb'] ?? 0;

// 3. Moyenne (Sécurisé pour PHP 8)
$moy_res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(score) as moy FROM candidats"));
$moyenne = $moy_res['moy'] ?? 0;

// 4. Données pour le graphique (Répartition)
$potentiels = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as nb FROM candidats WHERE score >= 50 AND score < 75"))['nb'] ?? 0;
$faibles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as nb FROM candidats WHERE score < 50"))['nb'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>ATS Expert | Analytics Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; color: #1e293b; margin: 0; padding: 30px; }
        .container { max-width: 1100px; margin: auto; }
        
        /* Navigation */
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        h1 { font-size: 24px; font-weight: 700; margin: 0; color: #0f172a; }
        
        /* Cards Stats */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border-top: 4px solid #2563eb; }
        .stat-card h3 { margin: 0; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
        .stat-card p { margin: 8px 0 0; font-size: 26px; font-weight: 700; }

        /* Zone Graphique */
        .chart-container { background: white; padding: 25px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        
        /* Table */
        .table-card { background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; padding: 15px; text-align: left; font-size: 11px; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .badge { padding: 4px 10px; border-radius: 20px; font-weight: 600; font-size: 11px; }
        .bg-green { background: #dcfce7; color: #166534; }
        .bg-gray { background: #f1f5f9; color: #475569; }

        .btn { padding: 10px 18px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; display: inline-block; }
        .btn-blue { background: #2563eb; color: white; }
        .btn-logout { color: #ef4444; border: 1px solid #fecaca; margin-left: 10px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>📊 ATS Expert Analytics</h1>
        <div>
            <a href="index.php" class="btn btn-blue">+ Nouveau Candidat</a>
            <a href="logout.php" class="btn btn-logout">Déconnexion</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><h3>Candidats analysés</h3><p><?php echo $total; ?></p></div>
        <div class="stat-card" style="border-top-color:#10b981"><h3>Profils Élite (≥75%)</h3><p><?php echo $elite; ?></p></div>
        <div class="stat-card" style="border-top-color:#f59e0b"><h3>Score Moyen Global</h3><p><?php echo round($moyenne); ?>%</p></div>
    </div>

    <div class="chart-container">
        <h3 style="margin-top:0; font-size:14px; color:#64748b; margin-bottom:20px;">RÉPARTITION DES TALENTS</h3>
        <canvas id="talentChart" style="max-height: 250px;"></canvas>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Nom du Candidat</th>
                    <th>Poste Visé</th>
                    <th>Score</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = mysqli_query($conn, "SELECT * FROM candidats ORDER BY score DESC");
                if(mysqli_num_rows($res) > 0) {
                    while($row = mysqli_fetch_assoc($res)) {
                        $c = ($row['score'] >= 75) ? 'bg-green' : 'bg-gray';
                        echo "<tr>";
                        echo "<td><strong>".htmlspecialchars($row['nom_candidat'])."</strong></td>";
                        echo "<td>".htmlspecialchars($row['nom_poste'])."</td>";
                        echo "<td><span class='badge $c'>".$row['score']."%</span></td>";
                        echo "<td style='text-align:right'>
                                <a href='fiche.php?id=".$row['id']."' style='text-decoration:none;'>👁️ Fiche</a>
                                <a href='supprimer.php?id=".$row['id']."' style='color:#ef4444; margin-left:15px; text-decoration:none;' onclick='return confirm(\"Supprimer?\")'>✕</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center; padding:30px; color:#94a3b8;'>Aucune donnée disponible.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// --- CONFIGURATION DU GRAPHIQUE ---
const ctx = document.getElementById('talentChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Élite (≥75%)', 'Potentiels (50-74%)', 'À revoir (<50%)'],
        datasets: [{
            label: 'Nombre de candidats',
            data: [<?php echo "$elite, $potentiels, $faibles"; ?>],
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
            borderRadius: 6,
            barThickness: 40
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
            x: { grid: { display: false } }
        }
    }
});
</script>

</body>
</html>