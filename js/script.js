document.addEventListener('DOMContentLoaded', function () {

    // Bouton "S'inscrire Premium" → redirige vers formulaire.php
    document.querySelectorAll('.btn-principal-offre').forEach(btn => {
        btn.addEventListener('click', () => {
            window.location.href = 'formulaire.php';
        });
    });

    // Highlight le lien nav actif
    const liens = document.querySelectorAll('nav a');
    const pageCourante = window.location.pathname.split('/').pop();
    liens.forEach(lien => {
        if (lien.getAttribute('href') === pageCourante) {
            lien.style.color = '#2dce6e';
            lien.style.borderBottomColor = '#2dce6e';
        }
    });

});

function verifier() {
    let username = document.getElementById("username")?.value;
    if (!username) {
        alert("Veuillez entrer votre nom d'utilisateur.");
        return false;
    }
    const boite = document.getElementById("merci-boite");
    if (boite) boite.style.display = "block";
    return true;
}
