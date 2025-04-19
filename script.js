document.addEventListener("DOMContentLoaded", function () {
    const dropdowns = document.querySelectorAll(".dropdown");

    dropdowns.forEach((dropdown) => {
        const toggle = dropdown.querySelector(".dropdown-toggle");
        const menu = dropdown.querySelector(".dropdown-menu");

        toggle.addEventListener("click", function (event) {
            event.preventDefault(); // Prevent default link behavior
            menu.classList.toggle("show");
        });
    });

    document.addEventListener("click", function (event) {
        dropdowns.forEach((dropdown) => {
            if (!dropdown.contains(event.target)) {
                dropdown.querySelector(".dropdown-menu").classList.remove("show");
            }
        });
    });
});


document.getElementById('hamburger-menu').addEventListener('click', function() {
    const navBar = document.querySelector('.nav-bar');
    navBar.classList.toggle('active');  // Toggle the 'active' class to show/hide the menu
});

const toggleBtn = document.querySelector('.toggle_btn');
    const navLinks = document.querySelector('.links');

    toggleBtn.addEventListener('click', () => {
        navLinks.classList.toggle('active');
    });
