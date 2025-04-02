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

// JavaScript for Mobile Dropdowns and Hamburger Menu
const hamburgerMenu = document.getElementById('hamburger-menu');
const navBar = document.querySelector('.nav-bar');
const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

hamburgerMenu.addEventListener('click', () => {
    navBar.classList.toggle('active');
});

// Mobile dropdown toggle
dropdownToggles.forEach(toggle => {
    toggle.addEventListener('click', (e) => {
        e.preventDefault(); // Prevent default link behavior
        const dropdownMenu = toggle.nextElementSibling; // The associated dropdown menu
        
        if (dropdownMenu) {
            dropdownMenu.classList.toggle('active'); // Toggle dropdown visibility
        }
    });
});

