document.addEventListener("DOMContentLoaded", function () {
    // Dropdown functionality
    const dropdowns = document.querySelectorAll(".dropdown");
    
    dropdowns.forEach((dropdown) => {
        const toggle = dropdown.querySelector(".dropdown-toggle");
        const menu = dropdown.querySelector(".dropdown-menu");
        
        toggle.addEventListener("click", function (event) {
            event.preventDefault();  // Prevent default link behavior
            menu.classList.toggle("show");  // Toggle the dropdown menu visibility
        });
    });

    // Close dropdown if clicked outside
    document.addEventListener("click", function (event) {
        dropdowns.forEach((dropdown) => {
            if (!dropdown.contains(event.target)) {
                dropdown.querySelector(".dropdown_menu").classList.remove("show");
            }
        });
    });

});

const toggleBtn = document.querySelector(".toggle_btn");
  const dropdownMenu = document.querySelector(".dropdown_menu");

  toggleBtn.addEventListener("click", () => {
    dropdownMenu.classList.toggle("open");
  });

const hamburger = document.querySelector('.hamburger');
const navLinks = document.querySelector('.nav-links');
let menuOpen = false;

hamburger.addEventListener('click', () => {
    if(menuOpen == false) {
        navLinks.computedStyleMap.display = "block";
        menuOpen = true;
    }
    else if (menuOpen == true) {
        navLinks.computedStyleMap.display = "none";
        menuOpen = false;
    }
});