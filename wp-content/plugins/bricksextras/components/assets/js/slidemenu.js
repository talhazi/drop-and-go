function xSlideMenu(){


  let extrasSlideMenu = function ( container ) {

    container.querySelectorAll('.brxe-xslidemenu').forEach( slideMenu => {

        const configAttr = slideMenu.getAttribute('data-x-slide-menu')
        const elementConfig = configAttr ? JSON.parse(configAttr) : {}
        let inBuilder = document.querySelector('.brx-body.iframe');

        let speedAnimation = elementConfig.slideDuration;

       

        /* add icons */
        slideMenu.querySelectorAll('.menu-item-has-children > a').forEach( menuHasChildren => {

          if ( menuHasChildren.querySelector('.x-slide-menu_dropdown-icon') ) {
            menuHasChildren.querySelector('.x-slide-menu_dropdown-icon').remove()
          }

            var btn = document.createElement("button");
                btn.setAttribute("aria-expanded", "false");
                btn.setAttribute("class", "x-slide-menu_dropdown-icon");
                btn.setAttribute("aria-label", elementConfig.subMenuAriaLabel);

            menuHasChildren.append(btn)

            if (slideMenu.querySelector('.x-sub-menu-icon')) {
              btn.innerHTML += slideMenu.querySelector('.x-sub-menu-icon').innerHTML
            } else {
              btn.innerHTML += '<svg class="x-slide-menu_dropdown-icon-svg" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/>';
            }

            btn.addEventListener('click', toggleSubMenu);
            
        
            function toggleSubMenu(e) {
                e.preventDefault()
                e.stopPropagation()
    
                let parent = this.closest(".menu-item-has-children");
                
                this.setAttribute("aria-expanded", "true" === this.getAttribute("aria-expanded") ? "false" : "true" );
                
                parent.lastElementChild.xslideToggle(speedAnimation)
    
                let allSiblings = Array.from(parent.parentElement.children).filter(sibling => sibling.textContent !== parent.textContent);
    
                allSiblings.forEach( dropDownSibling => {
    
                    if (dropDownSibling.classList.contains('menu-item-has-children') ){
                      
                        dropDownSibling.lastElementChild.xslideUp(speedAnimation)
    
                        dropDownSibling.children[0].lastElementChild.setAttribute("aria-expanded", "false")
                    }
    
                })
    
            }

        })
       
        /* current menu item open */
        if ( elementConfig.maybeExpandActive && slideMenu.querySelector('.current-menu-ancestor > a > .x-slide-menu_dropdown-icon') ) {
                             
           let currentAncestors = slideMenu.querySelectorAll('.current-menu-ancestor');

           currentAncestors.forEach( currentAncestor => {
              if ( currentAncestor && currentAncestor.querySelector('.sub-menu') && currentAncestor.querySelector('a > .x-slide-menu_dropdown-icon') ) {
                 currentAncestor.querySelector('.sub-menu').xslideDown(0);
                 currentAncestor.querySelector('a > .x-slide-menu_dropdown-icon').setAttribute('aria-expanded','true')
              }
           });

        }

        slideMenu.querySelectorAll('.menu-item-has-children > a[href*="#"]').forEach( menuHashLink => {

            menuHashLink.addEventListener('click', function(e) {

                e.preventDefault()
                e.stopPropagation()

                menuHashLink.querySelector('.x-slide-menu_dropdown-icon').click()

        })

        if (null != elementConfig.clickSelector && document.querySelectorAll(elementConfig.clickSelector).length) {

          slideMenu.querySelectorAll('.menu-item:not(.menu-item-has-children) > a[href*="#"]').forEach( menuHashLink => {

            if (inBuilder) {return}
            menuHashLink.addEventListener('click', function(e) {
              document.querySelector(elementConfig.clickSelector).click()
            })
          })

        }

      })

        if (null != elementConfig.clickSelector && document.querySelectorAll(elementConfig.clickSelector).length) {

            let slideMenuOpen = false;

            if (!inBuilder) {
              document.querySelectorAll(elementConfig.clickSelector).forEach(trigger => {

                      trigger.addEventListener('click', function(e) {

                        e.preventDefault()
                        e.stopPropagation()

                        if ( !slideMenuOpen ) { slideMenuOpen = true; } 
                        else {  slideMenuOpen = false;  }

                        if ( slideMenuOpen ) {
                          /* opening */
                          slideMenu.classList.add('x-slide-menu_open');
                          xOpenSlideMenu(slideMenu.getAttribute('data-x-id'))
                          

                          document.addEventListener('keydown', xEscClickCloseSlideMenu);
                          document.addEventListener('click', xEscClickCloseSlideMenu);

                        } 
                        
                        /* closing */
                        else {
                          slideMenu.classList.remove('x-slide-menu_open');
                          xCloseSlideMenu(slideMenu.getAttribute('data-x-id'))
                          

                          document.removeEventListener('keydown', xEscClickCloseSlideMenu);
                          document.removeEventListener('click', xEscClickCloseSlideMenu);
                        }


                    })

              })
            }

            function xEscClickCloseSlideMenu(e) {

              if((e.key === "Escape" || e.key === "Esc")){
                document.querySelector(elementConfig.clickSelector).click()
                return;
              }
          
              if (! e.target.closest('.x-slide-menu_open') && ! e.target.closest(elementConfig.clickSelector) ) {
                document.querySelector(elementConfig.clickSelector).click()
              }
          
            }
    
        }

        let slideMenuLinks = slideMenu.querySelectorAll('.menu-item > a');

        slideMenuLinks.forEach((slideMenuLink, index) => {
            slideMenuLink.addEventListener('keydown', (e) => {
                // Handle arrow up/down, home, and end keys
                if (e.key === 'ArrowUp' || e.key === 'ArrowDown' || e.key === 'Home' || e.key === 'End') {
                    e.preventDefault();
                    
                    // Get all visible menu links
                    const visibleLinks = [];
                    slideMenuLinks.forEach(link => {
                        // Check if link is visible (not in a hidden submenu)
                        const menuItem = link.closest('.menu-item');
                        const isInSubMenu = menuItem.closest('.sub-menu');
                        
                        // Include link if it's a top-level item or in a visible submenu
                        if (!isInSubMenu || (isInSubMenu && isInSubMenu.offsetHeight > 0 && isInSubMenu.style.display !== 'none')) {
                            visibleLinks.push(link);
                        }
                    });
                    
                    if (visibleLinks.length === 0) return;
                    
                    // Determine target index based on key pressed
                    let targetIndex;
                    
                    if (e.key === 'Home') {
                        // Navigate to first visible item
                        targetIndex = 0;
                    } else if (e.key === 'End') {
                        // Navigate to last visible item
                        targetIndex = visibleLinks.length - 1;
                    } else {
                        // Find current link index in visible links array
                        const currentIndex = visibleLinks.indexOf(slideMenuLink);
                        if (currentIndex === -1) return;
                        
                        if (e.key === 'ArrowUp') {
                            targetIndex = currentIndex === 0 ? visibleLinks.length - 1 : currentIndex - 1;
                        } else { // ArrowDown
                            targetIndex = currentIndex === visibleLinks.length - 1 ? 0 : currentIndex + 1;
                        }
                    }
                    
                    // Focus the target link
                    visibleLinks[targetIndex].focus();
                }
            });
        });

  })  

}

function xOpenSlideMenu(elementIdentifier) {

  const element = document.querySelector('.brxe-xslidemenu[data-x-id="' + elementIdentifier + '"]');
  if (!element) { return; }
  const configAttr = element.getAttribute('data-x-slide-menu');
  const elementConfig = configAttr ? JSON.parse(configAttr) : {}
  element.xslideDown(elementConfig.slideDuration)
  element.dispatchEvent(new Event('x_slide_menu:expand'))

}

function xCloseSlideMenu(elementIdentifier) {

  const element = document.querySelector('.brxe-xslidemenu[data-x-id="' + elementIdentifier + '"]');
  if (!element) { return; }
  const configAttr = element.getAttribute('data-x-slide-menu');
  const elementConfig = configAttr ? JSON.parse(configAttr) : {}

  element.xslideUp(elementConfig.slideDuration)
  element.dispatchEvent(new Event('x_slide_menu:collapse'))

}



extrasSlideMenu(document);

const xSlideMenuAJAX = xExtrasRegisterAJAXHandler('doExtrasSlideMenu');

              
// Expose functions
window.doExtrasSlideMenu = extrasSlideMenu;
window.xOpenSlideMenu = xOpenSlideMenu
window.xCloseSlideMenu = xCloseSlideMenu

if (typeof bricksextras !== 'undefined') {

  bricksextras.slidemenu = {
    expand: (brxParam) => {
      let target = brxParam?.target || false
      if ( target && target.hasAttribute('data-x-id')) {
        if ( 'none' === window.getComputedStyle(target, null).display ) {
         xOpenSlideMenu( target.getAttribute('data-x-id') )
        }
      }
    },
    collapse: (brxParam) => {
      let target = brxParam?.target || false
      if ( target && target.hasAttribute('data-x-id')) {
        if ( 'none' !== window.getComputedStyle(target, null).display ) {
          xCloseSlideMenu( target.getAttribute('data-x-id') )
        }
      }
    },
    toggle: (brxParam) => {
      let target = brxParam?.target || false
      if ( target && target.hasAttribute('data-x-id')) {
        if ( 'none' === window.getComputedStyle(target, null).display ) {
          xOpenSlideMenu( target.getAttribute('data-x-id') )
        } else {
          xCloseSlideMenu( target.getAttribute('data-x-id') )
        }
      }
    },
  }

}

}

document.addEventListener("DOMContentLoaded",function(e){
  bricksIsFrontend&&xSlideMenu()
})