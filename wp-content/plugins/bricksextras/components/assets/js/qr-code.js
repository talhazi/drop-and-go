function xQrCode() {
    
    
    // Process color values in QR code config
    function processQrCodeColors(config) {
        // Process dotsOptions colors
        if (config.dotsOptions) {
            if (config.dotsOptions.color) {
                const processedColor = xProcessBricksColor(config.dotsOptions.color);
                if (processedColor !== false) {
                    config.dotsOptions.color = processedColor;
                }
            }
            
            // Process gradient colors in dotsOptions
            if (config.dotsOptions.gradient && config.dotsOptions.gradient.colorStops) {
                config.dotsOptions.gradient.colorStops.forEach(stop => {
                    if (stop.color) {
                        const processedColor = xProcessBricksColor(stop.color);
                        if (processedColor !== false) {
                            stop.color = processedColor;
                        }
                    }
                });
            }
        }
        
        // Process cornersSquareOptions colors
        if (config.cornersSquareOptions) {
            if (config.cornersSquareOptions.color) {
                const processedColor = xProcessBricksColor(config.cornersSquareOptions.color);
                if (processedColor !== false) {
                    config.cornersSquareOptions.color = processedColor;
                }
            }
        }
        
        // Process cornersDotOptions colors
        if (config.cornersDotOptions) {
            if (config.cornersDotOptions.color) {
                const processedColor = xProcessBricksColor(config.cornersDotOptions.color);
                if (processedColor !== false) {
                    config.cornersDotOptions.color = processedColor;
                }
            }
        }
        
        // Always set background to transparent
        if (!config.backgroundOptions) {
            config.backgroundOptions = {};
        }
        
        // Force transparent background regardless of settings
        config.backgroundOptions.color = 'transparent';
        
        // Process gradient colors in backgroundOptions
        if (config.backgroundOptions.gradient && config.backgroundOptions.gradient.colorStops) {
            config.backgroundOptions.gradient.colorStops.forEach(stop => {
                if (stop.color) {
                    const processedColor = xProcessBricksColor(stop.color);
                    if (processedColor !== false) {
                        stop.color = processedColor;
                    }
                }
            });
        }
    }

    const extrasQrCode = function ( container ) {
        
        container.querySelectorAll('[data-x-qr-code]').forEach((element) => {
                // Skip if this element has already been initialized
                if (!element.hasAttribute('data-x-qr-init')) {
                    return;
                }

                const config = JSON.parse(element.getAttribute('data-x-qr-code'));
                
                const rect = element.getBoundingClientRect();
                const size = Math.max(Math.max(rect.width, 1), 300);
                
                const pixelRatio = window.devicePixelRatio || 1;
                
                if (config.type === 'canvas' || !config.type) {

                    if (!config.type) {
                        config.type = 'canvas';
                    }
                    
                    if (pixelRatio > 1) {
                        config.width = size * pixelRatio;
                        config.height = size * pixelRatio;
                        
                        config.cssWidth = size;
                        config.cssHeight = size;
                        
                    } else {
                        config.width = size;
                        config.height = size;
                    }
                } else {
                    config.width = size;
                    config.height = size;
                }
                

                processQrCodeColors(config);
                
                const qrCode = new QRCodeStyling(config);
                 
                qrCode.append(element);
                
                if (typeof window.xQrCode === 'undefined') {
                    window.xQrCode = {};
                }
                if (typeof window.xQrCode.Instances === 'undefined') {
                    window.xQrCode.Instances = {};
                }
                
                const elementId = element.getAttribute('data-x-id');
                
                if (elementId) {
                    window.xQrCode.Instances[elementId] = qrCode;
                }
                
                // Mark this element as initialized
                element.removeAttribute('data-x-qr-init');
        });
    }

    extrasQrCode(document);

    if ( document.querySelector('body > .brx-body.iframe') ) {
        setInterval(function() {
            extrasQrCode(document);
        }, 100);
    }

    const xQrCodeAJAX = xExtrasRegisterAJAXHandler('doExtrasQrCode');

    // Expose function
    window.doExtrasQrCode = extrasQrCode;
}

document.addEventListener("DOMContentLoaded",function(e){
    bricksIsFrontend&&xQrCode()
});