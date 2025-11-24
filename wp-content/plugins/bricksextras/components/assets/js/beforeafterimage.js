function xBeforeAfterImage() {

    if ( document.querySelector('body > .brx-body.iframe') ) {
        return
    }

    const extrasBeforeAfterImage = function ( container ) {

        container.querySelectorAll('.x-before-after').forEach((beforeAfter) => {

            const configAttr = beforeAfter.getAttribute('data-x-before-after')
            const config = configAttr ? JSON.parse(configAttr) : {}

            beforeAfter.querySelector('.x-before-after_slider').addEventListener('input', (e) => {
                beforeAfter.style.setProperty('--x-before-after-position', `${e.target.value}%`);
            })

            if ( config.maybeMouseMove ) {

                let valueHover = 0;

                function rangePosition(e) {
                    const container = beforeAfter.querySelector('.x-before-after_slider-container');
                    const rect = container.getBoundingClientRect();
                    
                    if ('horizontal' === config.direction) {
                        // If mouse is at left edge of container
                        if (e.clientX <= rect.left) return 0;
                        // If mouse is at right edge of container
                        if (e.clientX >= rect.right) return 100;
                        // Otherwise calculate position within container
                        return ((e.clientX - rect.left) / rect.width) * 100;
                    } else {
                        // If mouse is at top edge of container
                        if (e.clientY <= rect.top) return 0;
                        // If mouse is at bottom edge of container
                        if (e.clientY >= rect.bottom) return 100;
                        // Otherwise calculate position within container
                        return ((e.clientY - rect.top) / rect.height) * 100;
                    }
                }

                beforeAfter.querySelector('.x-before-after_slider-container').addEventListener('mousemove', function(e) {
                    beforeAfter.style.setProperty('--x-before-after-position', `${rangePosition(e)}%`)
                });
            }

        })

    }

    extrasBeforeAfterImage(document);

    const xBeforeAfterImageAJAX = xExtrasRegisterAJAXHandler('doExtrasBeforeAfterImage');

     // Expose function
     window.doExtrasBeforeAfterImage = extrasBeforeAfterImage; 
   

}

document.addEventListener("DOMContentLoaded",function(e){
    bricksIsFrontend&&xBeforeAfterImage()
 });