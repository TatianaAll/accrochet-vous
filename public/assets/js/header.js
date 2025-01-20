const hamb = document.querySelector('.menu.inMobile.isAbsolute');
const burgerMenu = document.querySelectorAll('.hamburgerMenu')
const nav = document.querySelector('.headerNavBar');

hamb.addEventListener('click',() => {
    nav.classList.toggle('navActive');
    burgerMenu.forEach((burger)=> {
        burger.classList.toggle('liActive');
    })
});


