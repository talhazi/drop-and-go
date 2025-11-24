function xBurgerTrigger(){

  if ( document.querySelector('body > .brx-body.iframe') ) {
    return
  }

  const extrasBurgerTrigger = function ( container ) {

      container.querySelectorAll('.brxe-xburgertrigger').forEach((burger) => {

        if (burger.hasAttribute('data-x-ready')) {
          return;
      }
      
        burger.setAttribute('aria-expanded', 'false')
        burger.setAttribute('data-x-ready', 'true');

        burger.addEventListener('click', toggleBurger)

        function closeBurger() {
          burger.setAttribute('aria-expanded', 'false')
          if ( burger.querySelector(".x-hamburger-box") ) {
            burger.querySelector(".x-hamburger-box").classList.remove("is-active")
          }
        }

        function openBurger() {
          burger.setAttribute('aria-expanded', 'true')
          if ( burger.querySelector(".x-hamburger-box") ) {
            burger.querySelector(".x-hamburger-box").classList.add("is-active")
          }
        }

        function toggleBurger() {

          if ('true' == burger.getAttribute('aria-expanded')) {
            closeBurger()
          } else {
            openBurger()
          }

        }

    });

  }


  extrasBurgerTrigger(document);

  const xBurgerTriggerAJAX = xExtrasRegisterAJAXHandler('extrasBurgerTrigger'); 


  // Expose function
  window.extrasBurgerTrigger = extrasBurgerTrigger;

}
    
document.addEventListener("DOMContentLoaded",function(e){
   bricksIsFrontend&&xBurgerTrigger()
});


