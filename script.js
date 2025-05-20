const hamburger = document.querySelector('.hamburger');
const navLinks = document.querySelector('.nav-links');

hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('open');
});

window.addEventListener('resize', () => {
    if (window.innerWidth > 768) {
        navLinks.style.display = '';
        menuOpen = false;
    }
});
