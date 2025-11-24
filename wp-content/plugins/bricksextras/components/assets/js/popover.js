
function xPopover(){

    const extrasPopover = function ( container, ajax = false ) {

        container.querySelectorAll('.brxe-xpopover').forEach( popover => {

            if (window.xTippy.Instances[popover.dataset.xId] && ajax && popover.closest('[data-query-element-id]') ) {
                return;
            }

            if ( ajax && popover.querySelector('.x-popover_button[aria-expanded]') ) {
                return;
            }

            const configAttr = popover.getAttribute('data-x-popover');
            const config = configAttr ? JSON.parse(configAttr) : {};

            let insideLoop = false; 
            let loopId = false; 
            let wrapperClass = '';

            if ( null != config.isLooping ) {
                insideLoop = true;
                loopEl = popover.closest('.brxe-' + config.isLooping);
                wrapperClass = 'brxe-' + config.isLooping;
                if (!loopEl) {
                    if ( null != config.isLoopingComponent ) {
                        loopEl = popover.closest('.brxe-' + config.isLoopingComponent)
                        wrapperClass = 'brxe-' + config.isLoopingComponent;
                    }  
                }
            }

            let appendBody = config.appendBody
            let mainWrapper

            let elementSelector = insideLoop ? loopEl.querySelector(config.elementSelector) : config.elementSelector;

            let tippyProps, tippyEl;

                if ( document.querySelector('body > .brx-body.iframe') ) {

                    tippyEl = popover.querySelector('.x-popover_button')
                    tippyProps = {
                        render(instance) {
                            const popper = popover.querySelector('[data-tippy-root]');
                            return {
                                popper,
                            };
                        },
                        allowHTML: true,     
                        interactive: true, 
                        arrow: true,
                        trigger: 'click',
                        appendTo: popover.querySelector('.x-popover_content'),
                        placement: config.placement,
                        maxWidth: 'none',    
                        animation: 'extras',
                        theme: 'extras',     
                        touch: true, 
                        moveTransition: 'transform ' + config.moveTransition + 'ms ease-out', 
                        offset: [ config.offsetSkidding , config.offsetDistance], 
                        
                    }

                } else {

                    const innerContent = config.multipleTriggers ? popover.querySelector('.x-popover_content-inner').innerHTML : popover.querySelector('.x-popover_content-inner');
                    const content = config.dynamicContent ? '' : innerContent;
                    const finalElementSelector = config.dynamicContent ? elementSelector + '[data-tippy-content]:not([data-tippy-content=""]' : elementSelector;

                    tippyEl = config.elementSelector ? finalElementSelector : popover.querySelector('.x-popover_button')

                    tippyProps = {
                        content: content, 
                        allowHTML: true,     
                        interactive: true, 
                        arrow: true,
                        trigger: config.interaction,
                        appendTo: appendBody ? document.body : popover.querySelector('.x-popover_content'),
                        placement: config.placement,
                        maxWidth: 'none',    
                        animation: 'extras',
                        theme: 'extras',     
                        touch: true, 
                        interactiveDebounce: 50,
                        followCursor: 'false' === config.followCursor ? false : config.followCursor,
                        delay: config.delay,
                        moveTransition: 'transform ' + config.moveTransition + 'ms ease-out', 
                        offset: [ config.offsetSkidding , config.offsetDistance], 
                        onCreate(instance) {

                            if ( appendBody ) {

                                const popoverElement = instance.popper;

                                popover.classList.forEach((className) => {
                                    popoverElement.classList.add(className);
                                });

                                popoverElement.classList.add('x-popover_footer');
                                
                                if (popover.id) {
                                    popoverElement.id = popover.id;
                                }

                                const popoverBox = instance.popper.querySelector('.tippy-box');

                                if ( popoverBox) {

                                    const popoverContent = popoverBox.querySelector('.tippy-content');

                                    if ( popoverContent ) {
                                
                                        // Create a wrapper div
                                        const wrapper = document.createElement('div');
                                        wrapper.classList.add('x-popover_content'); // Add your custom class to the wrapper

                                        // Wrap the popover content inside the wrapper
                                        popoverContent.parentNode.insertBefore(wrapper, popoverContent);
                                        wrapper.appendChild(popoverContent);

                                    }

                                }

                            }

                        },
                        onShow(instance) {

                            tippy.hideAll({exclude: instance})
                            setTimeout(() => {
                                if( typeof bricksLazyLoad === "function" ){
                                    bricksLazyLoad()
                                }
                            }, 50)
                            popover.addEventListener('keydown', function(e) {
                                if((e.key === "Escape" || e.key === "Esc")){
                                    instance.hide();
                                }
                            });

                            if ( appendBody && insideLoop ) {

                                const popoverElement = instance.popper;

                                /* loop wrapper element */
                                mainWrapper = document.createElement('div');
                                mainWrapper.className = wrapperClass;
                                mainWrapper.classList.add('x-popover_footer_loop');

                                
                                document.body.appendChild(mainWrapper);

                                
                                mainWrapper.appendChild(popoverElement);
                            }
                        },
                        onShown() {
                            popover.dispatchEvent(new Event('x_popover:show'))

                            document.addEventListener('keydown', function(e) {
                                if((e.key === "Escape" || e.key === "Esc")){
                                    tippy.hideAll()
                                }
                              });
                            
                            if ( popover.querySelector('.x-popover_button') ) {

                                popover.querySelector('.x-popover_button').addEventListener('keydown', function(e) {

                                    if ( e.code === 'Tab' && !e.shiftKey ){

                                            if ( popover.querySelector('.tippy-content .x-popover_content-inner') ) {

                                                popover.querySelector('.tippy-content').setAttribute('tabindex', '0')
                            
                                                if ( 
                                                    !popover.querySelector('.tippy-content a[href]:first-child') &&
                                                    !popover.querySelector('.tippy-content h1:first-child > a[href]')  && 
                                                    !popover.querySelector('.tippy-content h2:first-child > a[href]')  && 
                                                    !popover.querySelector('.tippy-content h3:first-child > a[href]')  && 
                                                    !popover.querySelector('.tippy-content h4:first-child > a[href]')  && 
                                                    !popover.querySelector('.tippy-content h5:first-child > a[href]')  &&
                                                    !popover.querySelector('.tippy-content h6:first-child > a[href]')  && 
                                                    !popover.querySelector('.tippy-content button:first-child') &&
                                                    !popover.querySelector('.tippy-content input:first-child') &&
                                                    !popover.querySelector('.tippy-content textarea:first-child') &&
                                                    !popover.querySelector('.tippy-content select:first-child') &&
                                                    !popover.querySelector('.tippy-content details:first-child') &&
                                                    !popover.querySelector('.tippy-content [tabindex]:not([tabindex="-1"]:first-child') ) {
                    
                                                        e.preventDefault()
                                                        popover.querySelector('.tippy-content').focus()
                    
                                                    }
                    
                                            }
                                        
                                    }
                                });
                            }
                            
                        },
                        onHide(instance) {
                            popover.dispatchEvent(new Event('x_popover:hide'))

                            if ( appendBody && insideLoop ) {

                                 const tooltipElement = instance.popper;
                            
                                 const wrapper = tooltipElement.parentNode;
                             
                                 if (wrapper) {
                                    wrapper.remove();
                                 }

                            }
                        }
                    };
                }  

                let xTippyInstance = tippy(tippyEl,tippyProps);
                window.xTippy.Instances[popover.dataset.xId] = xTippyInstance;

                if ( config.multipleTriggers && !document.querySelector('body > .brx-body.iframe') ) {
                    const singleton = tippy.createSingleton(xTippyInstance, tippyProps);
                }
                

                popover.addEventListener('x_popover:show', () => {

                    let container = popover.querySelector('.x-popover_content')

                    if ( container ) {

                        /* Pro Accordion */
                        if (typeof doExtrasAccordion == 'function') {
                            doExtrasAccordion(container)
                        }

                        /* Pro Slider */
                        if (typeof doExtrasSlider == 'function') {
                            doExtrasSlider(container)
                        }

                        /* Read More / Less */
                        if (typeof doExtrasReadmore == 'function') {
                            setTimeout(() => {
                                doExtrasReadmore(container)
                            }, 100);
                            
                        }

                        /* Dynamic Lightbox */
                        if (typeof doExtrasLightbox == 'function') {
                            doExtrasLightbox(container, true)
                        }
                    
                        /* Social share */
                        if (typeof doExtrasSocialShare == 'function') {
                            doExtrasSocialShare(container)
                        }
                    
                        /* OffCanvas */
                        if (typeof doExtrasOffCanvas == 'function') {
                            doExtrasOffCanvas(container)
                        }
                    
                        /* modal */
                        if (typeof doExtrasModal == 'function') {
                            doExtrasModal(container)
                        }

                         /* copy to clipboard */
                        if (typeof doExtrasCopyToClipBoard == 'function') {
                            doExtrasCopyToClipBoard(container)
                        }
                        if (typeof doExtrasCopyToClipBoardPopover == 'function') {
                            doExtrasCopyToClipBoardPopover(container)
                        }

                        /* media player */
                        if (typeof doExtrasMediaPlayer == 'function') {
                            doExtrasMediaPlayer(container)
                        }

                    }

                })

        })

    }

    extrasPopover(document);

    const xPopoverAjax = xExtrasRegisterAJAXHandler('doExtrasPopover', true);


    // Expose function
    window.doExtrasPopover = extrasPopover;

    if (typeof bricksextras !== 'undefined') {

        bricksextras.popover = {
          close: (brxParam) => {
            let target = brxParam?.target || false
            if ( target && target.getAttribute('data-x-id') ) {
                let popoverInstance = xTippy.Instances[target.getAttribute('data-x-id')]
                if (popoverInstance) {
                    popoverInstance.hide()
                }
            }
          },
          open: (brxParam) => {
            let target = brxParam?.target || false
            if ( target && target.getAttribute('data-x-id') ) {
                let popoverInstance = xTippy.Instances[target.getAttribute('data-x-id')]
                if (popoverInstance) {
                    popoverInstance.show()
                }
            }
          },
        }
      
      }

}

document.addEventListener("DOMContentLoaded",function(e){
    bricksIsFrontend&&xPopover()
})
