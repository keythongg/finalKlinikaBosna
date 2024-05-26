// functions.js

function showToast(message, type) {
    // Kreiranje elementa za toast
    var toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;

    // Postavljanje boje pozadine u zavisnosti od tipa
    if (type === 'success') {
        toast.style.backgroundColor = '#28a745'; // Zelena boja za uspeh
    } else if (type === 'error') {
        toast.style.backgroundColor = '#dc3545'; // Crvena boja za grešku
    } else {
        toast.style.backgroundColor = '#007bff'; // Plava boja za podrazumevani tip
    }

    // Dodavanje toast elementa u dokument
    document.getElementById('toast-container').appendChild(toast);

    // Uklanjanje toast elementa nakon nekoliko sekundi
    setTimeout(function() {
        document.getElementById('toast-container').removeChild(toast);
    }, 3000); // Toast će se ukloniti nakon 3 sekunde (3000 milisekundi)
}
