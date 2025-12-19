<?php
session_start();
if (!isset($_SESSION['logged_in'])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Scanner de CV | ATS Expert</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; display: flex; justify-content: center; padding: 20px; }
        .card { background: white; padding: 30px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 600px; }
        label { display: block; margin-top: 15px; font-weight: 600; color: #64748b; font-size: 12px; text-transform: uppercase; }
        input, textarea, select { width: 100%; padding: 12px; margin-top: 5px; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; }
        .upload-area { border: 2px dashed #2563eb; padding: 20px; background: #f8faff; text-align: center; border-radius: 8px; cursor: pointer; margin-top: 10px; transition: 0.3s; }
        .upload-area:hover { background: #eff6ff; }
        .btn { width: 100%; padding: 14px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 25px; }
        #status { font-size: 12px; margin-top: 5px; color: #10b981; font-weight: 600; display: none; }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="text-align:center">🔍 Analyse Intelligente par PDF</h2>
        <form action="traitement.php" method="POST">
            <label>Nom du Candidat</label>
            <input type="text" name="candidate_name" required>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px">
                <div><label>Poste</label><input type="text" name="job_title" required></div>
                <div><label>Ville</label><input type="text" name="target_city"></div>
            </div>

            <label>Niveau requis</label>
            <select name="min_etudes">
                <option value="Bac+2">Bac+2</option>
                <option value="Bac+3">Bac+3</option>
                <option value="Bac+5" selected>Bac+5</option>
            </select>
            
            <label>Compétences (ex: php, mysql, excel)</label>
            <input type="text" name="skills_required" placeholder="Séparez par des virgules">

            <label>Charger le CV (PDF uniquement)</label>
            <div class="upload-area" onclick="document.getElementById('cv_file').click()">
                <span id="upload-label">📁 Cliquez ici pour choisir le fichier PDF</span>
                <input type="file" id="cv_file" accept=".pdf" style="display:none">
                <div id="status">✅ Texte extrait avec succès !</div>
            </div>

            <textarea name="cv_content" id="cv_content" style="display:none"></textarea>
            
            <button type="submit" class="btn">Lancer l'Analyse</button>
            <a href="liste.php" style="display:block; text-align:center; margin-top:15px; color:#64748b; text-decoration:none;">Annuler</a>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js';
        
        const fileInput = document.getElementById('cv_file');
        const textArea = document.getElementById('cv_content');
        const status = document.getElementById('status');
        const label = document.getElementById('upload-label');

        fileInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            label.innerText = "⏳ Analyse du fichier en cours...";
            
            const reader = new FileReader();
            reader.onload = async function() {
                const typedarray = new Uint8Array(this.result);
                const pdf = await pdfjsLib.getDocument(typedarray).promise;
                let fullText = '';
                
                for (let i = 1; i <= pdf.numPages; i++) {
                    const page = await pdf.getPage(i);
                    const textContent = await page.getTextContent();
                    fullText += textContent.items.map(item => item.str).join(' ');
                }
                
                textArea.value = fullText;
                label.innerText = "📄 " + file.name;
                status.style.display = "block";
            };
            reader.readAsArrayBuffer(file);
        });
    </script>
</body>
</html>