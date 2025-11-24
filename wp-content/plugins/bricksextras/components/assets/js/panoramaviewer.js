
function xPanoramaViewer(){
    
    // Track active observers for debugging
    if (!window.xPanoramaObservers) {
        window.xPanoramaObservers = [];
    }
    
    // Track observer instances globally (survives DOM changes)
    if (!window.xPanoramaObserverInstances) {
        window.xPanoramaObserverInstances = {};
    }

    // Helper function to create custom icon with tooltip (for repeater hotspots)
    const createCustomIconWithTooltip = function(hotSpotDiv, args) {
        // Remove default Pannellum icon styling
        hotSpotDiv.style.backgroundImage = 'none';
        hotSpotDiv.style.width = 'auto';
        hotSpotDiv.style.height = 'auto';
        
        if (args.text) {
            hotSpotDiv.classList.add('pnlm-tooltip');
        }
        
        // Add accessibility attributes to the hotspot container
        hotSpotDiv.setAttribute('role', 'region');
        hotSpotDiv.setAttribute('aria-label', 'Interactive hotspot');
        
        const wrapper = document.createElement('div');
        wrapper.className = 'pnlm-hotspot-wrapper';
        
        // Generate unique ID for tooltip association
        const uniqueId = 'hotspot-tooltip-' + Math.random().toString(36).substr(2, 9);
        
        // Create accessible label from available text
        let accessibleLabel = 'Panorama hotspot';
        if (args.text) {
            // Strip HTML tags for clean aria-label
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = args.text;
            const cleanText = tempDiv.textContent || tempDiv.innerText || '';
            accessibleLabel = 'Panorama hotspot: ' + cleanText.trim();
        } else if (args.buttonText) {
            accessibleLabel = 'Panorama hotspot: ' + args.buttonText.trim();
        }
        
        // Create button or link wrapper for icon based on type
        let iconElement;
        if (args.type === 'link' && args.link) {
            iconElement = document.createElement('a');
            iconElement.className = 'pnlm-hotspot-icon';
            // Extract URL from link object or use string directly
            let url = '';
            if (typeof args.link === 'object') {
                // Handle URL being a string or array
                url = Array.isArray(args.link.url) ? (args.link.url[0] || '') : (args.link.url || '');
            } else {
                url = args.link;
            }
            iconElement.href = url || '#';
            if (typeof args.link === 'object' && args.link.newTab === true) {
                iconElement.target = '_blank';
                iconElement.rel = 'noopener noreferrer';
            }
            // Accessibility for links
            iconElement.setAttribute('aria-label', accessibleLabel);
            if (args.text) {
                iconElement.setAttribute('aria-describedby', uniqueId);
            }
        } else {
            iconElement = document.createElement('button');
            iconElement.className = 'pnlm-hotspot-icon';
            iconElement.type = 'button';
            // Accessibility for buttons
            iconElement.setAttribute('tabindex', '0');
            iconElement.setAttribute('aria-label', accessibleLabel);
            iconElement.setAttribute('role', 'button');
            if (args.text) {
                iconElement.setAttribute('aria-describedby', uniqueId);
                iconElement.setAttribute('aria-expanded', 'false');
            }
        }
        
        // Create inner span for icon content (sits above pulse)
        const iconSpan = document.createElement('span');
        iconSpan.className = 'pnlm-hotspot-icon-inner';
        iconSpan.setAttribute('aria-hidden', 'true'); // Hide decorative content from screen readers
        iconSpan.innerHTML = args.customIcon;
        
        // Add button text if provided (inside the icon-inner span)
        if (args.buttonText) {
            const textSpan = document.createElement('span');
            textSpan.className = 'pnlm-hotspot-text';
            textSpan.innerHTML = args.buttonText;
            iconSpan.appendChild(textSpan);
        }
        
        iconElement.appendChild(iconSpan);
        
        // Add icon element to wrapper
        wrapper.appendChild(iconElement);
        
        // Add tooltip span if text exists
        if (args.text) {
            const span = document.createElement('span');
            span.className = 'pnlm-hotspot-tooltip';
            span.id = uniqueId;
            span.setAttribute('role', 'tooltip');
            span.setAttribute('aria-hidden', 'true'); // Initially hidden
            span.innerHTML = args.text;
            wrapper.appendChild(span);
            
            // Add hover/focus events to manage tooltip visibility and camera movement
            const showTooltip = () => {
                span.setAttribute('aria-hidden', 'false');
                if (iconElement.tagName === 'BUTTON') {
                    iconElement.setAttribute('aria-expanded', 'true');
                }
            };
            
            const hideTooltip = () => {
                span.setAttribute('aria-hidden', 'true');
                if (iconElement.tagName === 'BUTTON') {
                    iconElement.setAttribute('aria-expanded', 'false');
                }
            };
            
            // Function to move camera to hotspot when focused
            const moveToHotspot = () => {
                // Find the panorama viewer instance
                const viewerElement = hotSpotDiv.closest('.x-panorama-viewer');
                
                if (viewerElement && viewerElement.pannellumInstance && args.pitch !== undefined && args.yaw !== undefined) {
                    try {
                        const viewer = viewerElement.pannellumInstance;
                        // Smoothly move camera to hotspot location
                        viewer.lookAt(args.pitch, args.yaw, undefined, 1000);
                    } catch (e) {
                        console.warn('Could not move camera to hotspot:', e);
                    }
                }
            };
            
            iconElement.addEventListener('mouseenter', showTooltip);
            iconElement.addEventListener('mouseleave', hideTooltip);
            iconElement.addEventListener('focus', () => {
                showTooltip();
                moveToHotspot();
            });
            iconElement.addEventListener('blur', hideTooltip);
        }
        
        // Insert wrapper into hotspot
        hotSpotDiv.innerHTML = '';
        hotSpotDiv.appendChild(wrapper);
    };


    // Helper function to process DOM hotspots from .x-hotspot-data container
    const processDOMHotspots = function(sceneElement, sceneId = null, config = null) {
        const hotspots = [];
        
        // Get the main element config once for all hotspots
        let mainElementConfig = null;
        const bricksElement = sceneElement.closest('.brxe-xpanoramaviewer');
        if (bricksElement) {
            const configAttr = bricksElement.getAttribute('data-panorama-config');
            if (configAttr) {
                try {
                    mainElementConfig = JSON.parse(configAttr);
                } catch (e) {
                    console.warn('Error parsing main element config:', e);
                }
            }
        }
        
        // Find hotspot data container in the scene element
        const hotspotContainer = sceneElement.querySelector('.x-hotspot-data');
        if (!hotspotContainer) {
            return hotspots;
        }
        
        // Process each hotspot item
        const hotspotItems = hotspotContainer.querySelectorAll('.x-hotspot-item');
        hotspotItems.forEach((item, index) => {
            const pitch = parseFloat(item.dataset.pitch) || 0;
            const yaw = parseFloat(item.dataset.yaw) || 0;
            const type = item.dataset.type || 'info';
            
            // Extract content from child elements
            const iconElement = item.querySelector('.x-hotspot-icon');
            const textElement = item.querySelector('.x-hotspot-text');
            const buttonTextElement = item.querySelector('.x-hotspot-button-text');
            
            let iconHTML = '';
            if (iconElement && iconElement.innerHTML.trim()) {
                iconHTML = iconElement.innerHTML;
            } else {
                // Default icon based on type
                if (type === 'scene') {
                    iconHTML = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M5.205 8.5a1.875 1.875 0 1 0 3.75 0 1.875 1.875 0 1 0 -3.75 0Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path><path d="M20.2 16.955 15.828 10.4a0.844 0.844 0 0 0 -1.393 -0.015L12.19 13.6l-1.39 -1.117a0.843 0.843 0 0 0 -1.229 0.191l-2.865 4.3" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path><path d="M2.25 3.023a1.5 1.5 0 0 0 -1.5 1.5v14.954a1.5 1.5 0 0 0 1.5 1.5h19.5a1.5 1.5 0 0 0 1.5 -1.5v-12a1.5 1.5 0 0 0 -0.426 -1.047l-2.883 -2.955a1.5 1.5 0 0 0 -1.074 -0.452Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>';
                } else if (type === 'link') {
                    iconHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M18.364 15.536 16.95 14.12l1.414 -1.414a5 5 0 1 0 -7.071 -7.071L9.879 7.05 8.464 5.636 9.88 4.222a7 7 0 0 1 9.9 9.9l-1.415 1.414zm-2.828 2.828 -1.415 1.414a7 7 0 0 1 -9.9 -9.9l1.415 -1.414L7.05 9.88l-1.414 1.414a5 5 0 1 0 7.071 7.071l1.414 -1.414 1.415 1.414zm-.708 -10.607 1.415 1.415 -7.071 7.07 -1.415 -1.414 7.071 -7.07z" fill="currentColor"/></svg>';
                } else {
                    iconHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g><path d="m9 23.25 -2.76 -3.11a1.55 1.55 0 1 1 2.32 -2.07l1.94 2.18v-9a1.5 1.5 0 0 1 3 0V18H15a4.49 4.49 0 0 1 4.5 4.5v0.75" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path><path d="M17.25 13.57a7.5 7.5 0 1 0 -10.5 0" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></g></svg>';
                }
            }
            
            const text = textElement ? textElement.innerHTML : '';
            const buttonText = buttonTextElement ? buttonTextElement.textContent : '';
            
            // Build hotspot object
            const hotspot = {
                pitch: pitch,
                yaw: yaw,
                type: type,
                createTooltipFunc: createCustomIconWithTooltip,
                createTooltipArgs: {
                    customIcon: iconHTML,
                    text: text,
                    type: type,
                    buttonText: buttonText,
                    pitch: pitch,
                    yaw: yaw,
                    config: config,
                    mainElementConfig: mainElementConfig
                }
            };
            
            // Add type-specific properties
            if (type === 'link' && item.dataset.link) {
                hotspot.createTooltipArgs.link = {
                    url: item.dataset.link,
                    newTab: true // Default to new tab for safety
                };
            } else if (type === 'scene' && item.dataset.sceneId) {
                hotspot.sceneId = item.dataset.sceneId;
            }
            
            hotspots.push(hotspot);
        });
        
        return hotspots;
    };

    const extrasPanoramaViewer = function ( container ) {

        bricksQuerySelectorAll(container, '.brxe-xpanoramaviewer').forEach( viewerRoot => { 

            // Get viewer ID once for this entire scope - must be first!
            const viewerId = viewerRoot.getAttribute('data-x-id');

            // Check if Pannellum is loaded
            if (typeof pannellum === 'undefined') {
                console.error('Pannellum library not loaded');
                return;
            }

            // Get the inner wrapper for Pannellum
            const viewer = viewerRoot.querySelector('.x-panorama-viewer');
            if (!viewer) {
                console.error('Panorama viewer wrapper not found');
                return;
            }

            // Check if in builder (declare once at top level)
            const isBuilderEnv = document.querySelector('body > .brx-body.iframe');
            
            // Check if already initializing (prevent race conditions)
            if (viewer.dataset.initializing === 'true') {
                return;
            }
            
            // Check if already initialized
            if (viewer.pannellumInstance) {
                // In builder, destroy and reinitialize to reflect changes
                if (isBuilderEnv) { 
                    
                    // Capture currently active scene before destroying instance
                    try {
                        if (viewer.pannellumInstance.getScene) {
                            preservedActiveScene = viewer.pannellumInstance.getScene();
                            if (preservedActiveScene) {
                                viewer.dataset.preservedActiveScene = preservedActiveScene;
                            }
                        }
                    } catch (e) {
                        
                    }
                    
                    // Dispose WebGL context before destroying instance
                    const canvas = viewer.querySelector('canvas');
                    if (canvas) {
                        const webglContext = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
                        if (webglContext) {
                            const loseContext = webglContext.getExtension('WEBGL_lose_context');
                            if (loseContext) {
                                loseContext.loseContext();
                            }
                        }
                    }
                    
                    viewer.pannellumInstance.destroy();
                    viewer.pannellumInstance = null;
                    
                    // Clean up any leftover Pannellum DOM elements
                    const pannellumUI = viewer.querySelectorAll('.pnlm-ui, .pnlm-render-container');
                    pannellumUI.forEach(el => el.remove());
                    
                    // Remove Pannellum classes from viewer
                    viewer.classList.remove('pnlm-container', 'pnlm-grab', 'pnlm-grabbing');
                    viewer.removeAttribute('tabindex');
                } else {
                    // Frontend: skip if already initialized
                    return;
                }
            }

            // Mark as initializing to prevent race conditions
            viewer.dataset.initializing = 'true';
            
            // Initialize observer storage array
            if (!viewer.xPanoramaObservers) {
                viewer.xPanoramaObservers = [];
            }

            // Get configuration from data attribute on root
            const configAttr = viewerRoot.getAttribute('data-panorama-config');
            if (!configAttr) {
                console.error('No panorama configuration found');
                return;
            }

            let config;
            try {
                config = JSON.parse(configAttr);
            } catch (e) {
                console.error('Invalid panorama configuration:', e);
                return;
            }

            // Function to create debug overlay
            const createDebugOverlay = (instance, config) => {
                if (!isBuilderEnv) return;
                
                // Clean up any existing debug observers first
                const debugObserverId = `Debug-${viewerId}`;
                if (window.xPanoramaObserverInstances[debugObserverId]) {
                    const index = window.xPanoramaObservers.indexOf(debugObserverId);
                    if (index > -1) {
                        window.xPanoramaObservers.splice(index, 1);
                    }
                    window.xPanoramaObserverInstances[debugObserverId].disconnect();
                    delete window.xPanoramaObserverInstances[debugObserverId];
                }
                
                // Remove any existing debug overlay first
                const existingOverlay = viewer.querySelector('.pnlm-debug-overlay');
                if (existingOverlay) {
                    existingOverlay.remove();
                }
                
                // Check if debug mode is enabled in config
                const debugMode = config.hotSpotDebug || (config.default && config.default.hotSpotDebug);
                
                // Create debug overlay
                const debugOverlay = document.createElement('div');
                debugOverlay.className = 'pnlm-debug-overlay';
                debugOverlay.style.cssText = 'position: fixed; top: 10px; left: 10px; background: rgba(0,0,0,0.8); color: #fff; padding: 10px 15px; border-radius: 4px; font-family: monospace; font-size: 12px; z-index: 99999; pointer-events: auto; line-height: 1.6;';
                
                // Make it completely isolated from Bricks events
                debugOverlay.setAttribute('data-brx-ignore', 'true');
                debugOverlay.setAttribute('data-no-bricks', 'true');
                
                // Hide by default if debug mode is disabled
                if (!debugMode) {
                    debugOverlay.style.display = 'none';
                }
                
                viewer.appendChild(debugOverlay);
                
                // Function to copy to clipboard
                const copyToClipboard = async (text, clickedElement) => {
                    try {
                        await navigator.clipboard.writeText(text);
                        // Animate opacity feedback
                        clickedElement.style.transition = 'opacity 0.15s ease';
                        clickedElement.style.opacity = '0.5';
                        setTimeout(() => {
                            clickedElement.style.opacity = '1';
                        }, 150);
                    } catch (err) {
                        console.warn('Failed to copy to clipboard:', err);
                    }
                };
                
                // Update overlay on mouse move
                const updateDebugInfo = () => {
                    const pitch = instance.getPitch().toFixed(2);
                    const yaw = instance.getYaw().toFixed(2);
                    debugOverlay.innerHTML = `<strong>Current View:</strong><br>Pitch: <span class="clickable-value" data-value="${pitch}" data-type="Pitch" style="cursor: pointer; text-decoration: underline; color: #4CAF50;">${pitch}°</span><br>Yaw: <span class="clickable-value" data-value="${yaw}" data-type="Yaw" style="cursor: pointer; text-decoration: underline; color: #4CAF50;">${yaw}°</span>`;
                    
                    // Add click listeners to clickable values
                    debugOverlay.querySelectorAll('.clickable-value').forEach(span => {
                        span.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            e.stopImmediatePropagation();
                            const value = span.getAttribute('data-value');
                            copyToClipboard(value, span);
                        });
                    });
                };
                
                // Update on mouse move
                viewer.addEventListener('mousemove', updateDebugInfo);
                
                // Initial update
                updateDebugInfo();
                
                // Watch for config changes to toggle visibility (only if not already observing)
                const debugId = `Debug-${viewerId}`;
                if (!window.xPanoramaObserverInstances[debugId] && !window.xPanoramaObservers.includes(debugId)) {
                    window.xPanoramaObservers.push(debugId);
                    const configObserver = new MutationObserver(() => {
                        const configAttr = viewerRoot.getAttribute('data-panorama-config');
                        if (configAttr) {
                            try {
                                const currentConfig = JSON.parse(configAttr);
                                const isDebugEnabled = currentConfig.hotSpotDebug || (currentConfig.default && currentConfig.default.hotSpotDebug);
                                debugOverlay.style.display = isDebugEnabled ? '' : 'none';
                            } catch (e) {
                                // Ignore parse errors
                            }
                        }
                    });
                    
                    configObserver.observe(viewerRoot, {
                        attributes: true,
                        attributeFilter: ['data-panorama-config']
                    });
                    
                    // Store observer reference globally to survive DOM changes
                    window.xPanoramaObserverInstances[debugId] = configObserver;
                }
            };
            
            // Function to initialize the viewer
            const initViewer = () => {
                let preservedActiveScene = viewer.dataset.preservedActiveScene || null;
                if (preservedActiveScene) {
                    delete viewer.dataset.preservedActiveScene;
                }
                // Check for child scenes to build tour (allow scenes nested in divs for query loops)
                const childScenes = Array.from(viewerRoot.querySelectorAll('.brxe-xpanoramascene'));
                const hasTour = childScenes.length > 0;
                
                if (hasTour) {
                // Tour mode - build multi-scene config
                const scenes = {};
                let firstSceneId = null;
                 
                // Add child scenes
                childScenes.forEach((sceneEl, index) => {
                    const sceneConfigAttr = sceneEl.getAttribute('data-panorama-config');
                    
                    if (sceneConfigAttr) {
                        try {
                            const sceneConfig = JSON.parse(sceneConfigAttr);
                            const sceneId = sceneConfig.sceneId || sceneEl.getAttribute('data-x-id') || 'scene-' + Math.random().toString(36).substr(2, 9);
                            
                            // Update the scene config with the resolved scene ID
                            sceneConfig.sceneId = sceneId;
                            scenes[sceneId] = sceneConfig;
                            
                            // Update the DOM attribute so hotspot processing can find the correct scene ID
                            sceneEl.setAttribute('data-panorama-config', JSON.stringify(sceneConfig));
                            
                            // Set first scene ID from first child
                            if (index === 0) {
                                firstSceneId = sceneId;
                            }
                            
                            // Hide the child scene element
                            sceneEl.style.display = 'none';
                        } catch (e) {
                            console.error('Invalid scene config:', e);
                        }
                    }
                });

                // In builder: Override firstSceneId with captured active scene if available
                if (isBuilderEnv) {
                    
                    if (preservedActiveScene && scenes[preservedActiveScene]) {
                        firstSceneId = preservedActiveScene;
                    } else {
                    }
                }

                if (Object.keys(scenes).length > 0 && firstSceneId) {
                    
                    // Save viewer settings before rebuilding config
                    const hotSpotDebug = config.hotSpotDebug;
                    const viewerYaw = config.yaw;
                    const viewerPitch = config.pitch;
                    const viewerHfov = config.hfov;
                    const viewerAutoRotate = config.autoRotate;
                    
                    // Build tour config using parent's control settings
                    config = {
                        default: {
                            firstScene: firstSceneId,
                            sceneFadeDuration: 1000,
                            autoLoad: true,
                            showZoomCtrl: config.showZoomCtrl !== undefined ? config.showZoomCtrl : true,
                            showFullscreenCtrl: config.showFullscreenCtrl !== undefined ? config.showFullscreenCtrl : true,
                            mouseZoom: config.mouseZoom !== undefined ? config.mouseZoom : true,
                            mouseZoomSensitivity: config.mouseZoomSensitivity !== undefined ? parseFloat(config.mouseZoomSensitivity) : 1,
                            friction: config.friction !== undefined ? parseFloat(config.friction) : 0.5,
                            draggable: config.draggable !== undefined ? config.draggable : true,
                            disableKeyboardCtrl: config.disableKeyboardCtrl !== undefined ? config.disableKeyboardCtrl : false,
                            hotSpotDebug: hotSpotDebug !== undefined ? hotSpotDebug : false,
                            autoRotate: viewerAutoRotate !== undefined ? parseFloat(viewerAutoRotate) : undefined,
                        },
                        scenes: scenes 
                    };
                    
                    // Apply viewer's initial view settings to all scenes if not set on individual scenes
                    Object.keys(scenes).forEach(sceneId => {
                        const scene = scenes[sceneId];
                        if (scene) {
                            if (scene.yaw === undefined && viewerYaw !== undefined) {
                                scene.yaw = parseFloat(viewerYaw);
                            }
                            if (scene.pitch === undefined && viewerPitch !== undefined) {
                                scene.pitch = parseFloat(viewerPitch);
                            }
                            if (scene.hfov === undefined && viewerHfov !== undefined) {
                                scene.hfov = parseFloat(viewerHfov);
                            }
                            if (scene.autoRotate === undefined && viewerAutoRotate !== undefined) {
                                scene.autoRotate = parseFloat(viewerAutoRotate);
                            }
                        }
                    });
                }
            }

            // Add DOM hotspots from nested .brxe-xpanoramahotspot elements
            if (config.scenes) {
                // Tour mode - find hotspots nested in each scene element
                childScenes.forEach((sceneEl) => {
                    const sceneConfigAttr = sceneEl.getAttribute('data-panorama-config');
                    if (!sceneConfigAttr) return;
                    
                    try {
                        const sceneConfig = JSON.parse(sceneConfigAttr);
                        const sceneId = sceneConfig.sceneId;
                        
                        if (!sceneId || !config.scenes[sceneId]) return;
                        
                        // Process DOM hotspots from .x-hotspot-data container
                        const domHotspots = processDOMHotspots(sceneEl, sceneId, config);
                        if (domHotspots.length > 0) {
                            // Initialize hotSpots array if it doesn't exist
                            if (!config.scenes[sceneId].hotSpots) {
                                config.scenes[sceneId].hotSpots = [];
                            }
                            // Add DOM hotspots to the scene
                            config.scenes[sceneId].hotSpots = config.scenes[sceneId].hotSpots.concat(domHotspots);
                        }
                        
                        // Find all hotspot elements nested in this scene
                        const hotspotElements = sceneEl.querySelectorAll('.brxe-xpanoramahotspot');
                        
                        hotspotElements.forEach((hotspotEl, index) => {
                            // Get hotspot config from data attribute if exists
                            const hotspotConfigAttr = hotspotEl.getAttribute('data-hotspot-config');
                            let hotspotConfig = {
                                pitch: 0,
                                yaw: 0,
                                type: 'info'
                            };
                            
                            if (hotspotConfigAttr) {
                                try {
                                    hotspotConfig = { ...hotspotConfig, ...JSON.parse(hotspotConfigAttr) };
                                } catch (e) {
                                    console.error('Invalid hotspot config:', e);
                                }
                            }
                            
                            // Create unique selector for this hotspot
                            const hotspotId = hotspotEl.id || `hotspot-${sceneId}-${index}`;
                            if (!hotspotEl.id) {
                                hotspotEl.id = hotspotId;
                            }
                            
                            // Initialize hotSpots array if needed
                            if (!config.scenes[sceneId].hotSpots) {
                                config.scenes[sceneId].hotSpots = [];
                            }
                            
                            // Check if this hotspot already exists (prevent duplicates)
                            const existingHotspot = config.scenes[sceneId].hotSpots.find(hs => 
                                hs.createTooltipArgs === `#${hotspotId}`
                            );
                            
                            if (existingHotspot) {
                                return;
                            }
                            
                            // Build hotspot object with button + tooltip structure
                            const hotspotObj = {
                                pitch: hotspotConfig.pitch,
                                yaw: hotspotConfig.yaw,
                                type: hotspotConfig.type,
                                createTooltipFunc: (hotSpotDiv, args) => {
                                    // Remove default Pannellum icon styling
                                    hotSpotDiv.style.backgroundImage = 'none';
                                    hotSpotDiv.style.width = 'auto';
                                    hotSpotDiv.style.height = 'auto';
                                    
                                    // Create wrapper with position relative
                                    const wrapper = document.createElement('div');
                                    wrapper.className = 'pnlm-hotspot-wrapper';
                                    
                                    // Create button wrapper for icon
                                    const button = document.createElement('button');
                                    button.className = 'pnlm-hotspot-icon';
                                    button.type = 'button';
                                    
                                    // Create inner span for icon content (sits above pulse)
                                    const iconSpan = document.createElement('span');
                                    iconSpan.className = 'pnlm-hotspot-icon-inner';
                                    
                                    // Use custom icon or default SVG
                                    if (hotspotConfig.customIcon) {
                                        iconSpan.innerHTML = hotspotConfig.customIcon;
                                    } else {
                                        iconSpan.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 26 26"><circle fill-opacity=".78" cy="13" cx="13" r="11" fill="currentColor"/></svg>';
                                    }
                                    
                                    button.appendChild(iconSpan);
                                    
                                    // Add button to wrapper
                                    wrapper.appendChild(button);
                                    
                                    // Get the content from the nested element
                                    const sourceElement = document.querySelector(args);
                                    if (sourceElement) {
                                        // Add tooltip class to hotspot
                                        hotSpotDiv.classList.add('pnlm-tooltip');
                                        
                                        // Create tooltip span
                                        const tooltipSpan = document.createElement('span');
                                        tooltipSpan.className = 'pnlm-hotspot-tooltip';
                                        
                                        // Move all children from source to tooltip
                                        while (sourceElement.firstChild) {
                                            tooltipSpan.appendChild(sourceElement.firstChild);
                                        }
                                        
                                        wrapper.appendChild(tooltipSpan);
                                    }
                                    
                                    // Insert wrapper into hotspot
                                    hotSpotDiv.innerHTML = '';
                                    hotSpotDiv.appendChild(wrapper);
                                },
                                createTooltipArgs: `#${hotspotId}`
                            };
                            
                            // Add custom CSS class if specified
                            if (hotspotConfig.cssClass) {
                                hotspotObj.cssClass = hotspotConfig.cssClass;
                            }
                            
                            // Add hotspot
                            config.scenes[sceneId].hotSpots.push(hotspotObj);
                        });
                    } catch (e) {
                        console.error('Error processing scene hotspots:', e);
                    }
                });
            }

            // Process DOM hotspots for single scenes (non-tour mode)
            if (!hasTour) {
                const domHotspots = processDOMHotspots(viewerRoot, null, config);
                if (domHotspots.length > 0) {
                    if (!config.hotSpots) {
                        config.hotSpots = [];
                    }
                    config.hotSpots = config.hotSpots.concat(domHotspots);
                }
            }

            // Disable compass by default
            config.compass = false;

            // Restore saved view state from localStorage (builder only)
            if (isBuilderEnv && viewerId) {
                try {
                    // For tours, restore the active scene's view state
                    if (hasTour) {
                        const activeScene = viewerRoot.querySelector('.brxe-xpanoramascene.is-active-element');
                        if (activeScene) {
                            const activeSceneConfig = JSON.parse(activeScene.getAttribute('data-panorama-config'));
                            const sceneId = activeSceneConfig.sceneId;
                            
                            const savedView = localStorage.getItem(`panorama_view_${viewerId}_${sceneId}`);
                            
                            if (savedView) {
                                const viewState = JSON.parse(savedView);
                                const age = Date.now() - viewState.timestamp;
                                
                                if (age < 300000) { // 5 minutes
                                    if (config.scenes && config.scenes[sceneId]) {
                                        config.scenes[sceneId].yaw = viewState.yaw;
                                        config.scenes[sceneId].pitch = viewState.pitch;
                                        config.scenes[sceneId].hfov = viewState.hfov
                                    }
                                }
                            }
                        }
                    } else {
                        // For single scenes, restore to main config
                        const savedView = localStorage.getItem(`panorama_view_${viewerId}_default`);
                        if (savedView) {
                            const viewState = JSON.parse(savedView);
                            const age = Date.now() - viewState.timestamp;
                            if (age < 300000) {
                                config.yaw = viewState.yaw;
                                config.pitch = viewState.pitch;
                                config.hfov = viewState.hfov;
                            }
                        }
                    }
                } catch (e) {
                    console.warn('Could not restore saved view state in main init:', e);
                }
            }

            // Get original config from data attribute for overlay creation
            const originalConfigAttr = viewerRoot.getAttribute('data-panorama-config');
            let originalConfig = {};
            if (originalConfigAttr) {
                try {
                    originalConfig = JSON.parse(originalConfigAttr);
                } catch (e) {
                    console.warn('Could not parse original config:', e);
                }
            }
            
            // Create scroll instructions overlay if enabled (before Pannellum instance)
            let scrollOverlay = null;
            if (originalConfig.showScrollInstructions) {
                scrollOverlay = document.createElement('div');
                scrollOverlay.className = 'pnlm-scroll-instructions';
                scrollOverlay.style.opacity = '0';
                scrollOverlay.style.pointerEvents = 'none';
                
                // Get custom text or use defaults
                const isMac = /Mac|iPhone|iPad|iPod/.test(navigator.userAgent);
                const keyText = isMac ? '⌘' : 'Ctrl';
                
                let desktopText = originalConfig.scrollInstructionsDesktopText || 'Use {x_key} + scroll to zoom';
                desktopText = desktopText.replace('{x_key}', keyText);
                
                scrollOverlay.innerHTML = desktopText;
                
                viewer.appendChild(scrollOverlay);
            }

            // Show loading spinner (or preview in builder if enabled)
            const previewSpinner = isBuilderEnv && viewerRoot.getAttribute('data-preview-spinner') === 'true';
            
            if (previewSpinner) {
                // In builder with preview enabled, show spinner permanently
                viewer.setAttribute('data-loading', 'true');
            } else if (!isBuilderEnv) {
                // On frontend, show spinner during actual loading
                viewer.setAttribute('data-loading', 'true');
            }

            // Initialize the viewer
            try {
                const instance = pannellum.viewer(viewer, config); 
                
                // Store instance reference
                viewer.pannellumInstance = instance;
                
                // Hide spinner when panorama loads (only on frontend)
                if (!previewSpinner) {
                    instance.on('load', () => {
                        // Delay removing attribute to allow fade transition to complete
                        setTimeout(() => {
                            viewer.setAttribute('data-loading', 'false');
                        }, 0);
                    });
                    
                    // Also hide spinner on error to prevent stuck spinner
                    instance.on('error', () => {
                        setTimeout(() => {
                            viewer.setAttribute('data-loading', 'false');
                        }, 0);
                    });
                }
                
                // Hide scroll overlay when user actually starts panning/interacting with panorama
                if (scrollOverlay) {
                    const hideOverlay = () => {
                        if (scrollOverlay) {
                            scrollOverlay.style.opacity = '0';
                        }
                    };
                    
                    // Only hide when user starts panning (mousemove after mousedown)
                    let isPanning = false;
                    
                    instance.on('mousedown', () => {
                        isPanning = false; // Reset panning state
                    });
                    
                    // Hide overlay when user starts panning (moving mouse while pressed)
                    viewer.addEventListener('mousemove', (e) => {
                        if (e.buttons === 1 && !isPanning) { // Left mouse button is pressed
                            isPanning = true;
                            hideOverlay();
                        }
                    });
                    
                    // For touch devices, hide when user starts moving (touchmove with 2+ fingers)
                    viewer.addEventListener('touchmove', (e) => {
                        if (e.touches.length >= 2) {
                            hideOverlay();
                        }
                    });
                }
                
                // Check Pannellum's orientation detection and verify with actual gyro data
                const orientationSupported = instance.isOrientationSupported();
                
                if (orientationSupported) {
                    // Function to test gyro data
                    const testGyroData = () => {
                        const gyroTimeout = setTimeout(() => {
                            window.removeEventListener('deviceorientation', gyroHandler);
                        }, 500);
                        
                        const gyroHandler = (e) => {
                            clearTimeout(gyroTimeout);
                            window.removeEventListener('deviceorientation', gyroHandler);
                            
                            if (e.alpha !== null || e.beta !== null || e.gamma !== null) {
                                viewerRoot.setAttribute('data-orientation-supported', 'true');
                            }
                        };
                        
                        window.addEventListener('deviceorientation', gyroHandler);
                    };
                    
                    // Check if permission API exists (iOS 13+, or any future browser requiring permission)
                    if (typeof DeviceOrientationEvent !== 'undefined' && typeof DeviceOrientationEvent.requestPermission === 'function') {
                        // Try to check if permission was already granted
                        DeviceOrientationEvent.requestPermission()
                            .then(permission => {
                                if (permission === 'granted') {
                                    testGyroData();
                                }
                            })
                            .catch(() => {
                                // Permission check requires user gesture - add click handler
                            });
                        
                        // Request permission on first user interaction
                        viewer.addEventListener('click', async () => {
                            try {
                                const permission = await DeviceOrientationEvent.requestPermission();
                                if (permission === 'granted') {
                                    testGyroData();
                                }
                            } catch (error) {
                                // Permission denied or error
                            }
                        }, { once: true });
                    } else {
                        // No permission API - test directly
                        testGyroData();
                    }
                } 
                            
                // Add smooth zoom functionality to zoom buttons
                setTimeout(() => {
                    const zoomInBtn = viewer.querySelector('.pnlm-zoom-in');
                    const zoomOutBtn = viewer.querySelector('.pnlm-zoom-out');
                    
                    if (zoomInBtn) {
                        zoomInBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            const currentHfov = instance.getHfov();
                            const bounds = instance.getHfovBounds();
                            const newHfov = Math.max(currentHfov - 10, bounds[0]);
                            
                            // Use Pannellum's setHfov with 300ms animation
                            instance.setHfov(newHfov, 300);
                        }, true);
                    }
                    
                    if (zoomOutBtn) {
                        zoomOutBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            const currentHfov = instance.getHfov();
                            const bounds = instance.getHfovBounds();
                            const newHfov = Math.min(currentHfov + 10, bounds[1]);
                            
                            // Use Pannellum's setHfov with 300ms animation
                            instance.setHfov(newHfov, 300);
                        }, true);
                    }
                }, 0);
                
                // Clear initializing flag
                viewer.dataset.initializing = 'false';
                
                // Hide the inner content div when scene loads
                instance.on('load', () => {
                    if (!isBuilderEnv){
                        viewerRoot.setAttribute('data-panorama-loaded', 'true');
                    }
                });

                // Add save listener for main instance (builder only)
                if (isBuilderEnv && viewerId) {
                    let saveEnabled = false;
                    
                    // Enable saving after initial load is complete
                    instance.on('load', () => {
                        setTimeout(() => {
                            saveEnabled = true;
                        }, 1000); // Wait 1 second after load before enabling saves
                    });
                    
                    instance.on('animatefinished', () => {
                        if (!saveEnabled) {
                            return;
                        }
                        
                        try {
                            // Get current scene ID
                            let currentSceneId = null;
                            if (hasTour) {
                                currentSceneId = instance.getScene();
                            } else {
                                currentSceneId = 'default';
                            }
                            
                            if (currentSceneId) {
                                const currentView = {
                                    yaw: instance.getYaw(),
                                    pitch: instance.getPitch(),
                                    hfov: instance.getHfov(),
                                    timestamp: Date.now()
                                };
                                localStorage.setItem(`panorama_view_${viewerId}_${currentSceneId}`, JSON.stringify(currentView));
                            }
                        } catch (e) {
                            console.warn('Could not save view state from main instance:', e);
                        }
                    });
                }
                
                // Add back button for tours
                if (hasTour) {
                    const initialScene = config.default ? config.default.firstScene : Object.keys(config.scenes)[0];
                    let sceneHistory = [initialScene];
                    
                    // Track scene changes to build history
                    instance.on('scenechange', (sceneId) => {
                        // Show spinner when scene starts changing
                        viewer.setAttribute('data-loading', 'true');
                        
                        // Only add to history if it's different from current scene
                        if (sceneHistory[sceneHistory.length - 1] !== sceneId) {
                            sceneHistory.push(sceneId);
                            // Limit history to prevent memory issues
                            if (sceneHistory.length > 50) {
                                sceneHistory = sceneHistory.slice(-25);
                            } 
                            updateBackButton();
                        }
                    });
                    
                    // Hide spinner when scene finishes loading
                    instance.on('scenechangefadedone', () => {
                        setTimeout(() => {
                            viewer.setAttribute('data-loading', 'false');
                        }, 300);
                    });
                    
                    // Create back button
                    setTimeout(() => {
                        const controls = viewer.querySelector('.pnlm-controls-container');
                        if (controls) {
                            const backBtn = document.createElement('div');
                            backBtn.className = 'pnlm-back-button pnlm-sprite pnlm-controls pnlm-control x-panorama-back-btn';
                            backBtn.setAttribute('role', 'button');
                            backBtn.setAttribute('aria-label', 'Go back to previous scene');
                            backBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>';
                            backBtn.style.display = 'none'; // Hidden initially
                            
                            // Insert after other controls
                            controls.appendChild(backBtn);
                            
                            // Back button click handler
                            backBtn.addEventListener('click', (e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                
                                if (sceneHistory.length > 1) {
                                    // Remove current scene from history
                                    sceneHistory.pop();
                                    // Get previous scene
                                    const previousScene = sceneHistory[sceneHistory.length - 1];
                                    // Load previous scene (this will trigger scenechange event)
                                    instance.loadScene(previousScene);
                                    // Update button visibility immediately
                                    updateBackButton();
                                }
                            });
                            
                            // Function to update back button visibility
                            const updateBackButton = () => {
                                backBtn.style.display = sceneHistory.length > 1 ? '' : 'none';
                            };
                            
                            // Store reference for the updateBackButton function
                            window.updateBackButton = updateBackButton;
                        }
                    }, 100);
                }
                
                // Builder: Watch for active scene changes and config updates
                if (isBuilderEnv && hasTour) {
                    // Clean up existing observer if it exists, then create fresh one
                    const mainObserverId = `Main-${viewerId}`;
                    if (window.xPanoramaObserverInstances[mainObserverId]) {
                        
                        // Disconnect and delete old observer
                        window.xPanoramaObserverInstances[mainObserverId].disconnect();
                        delete window.xPanoramaObserverInstances[mainObserverId];
                        
                        // Remove from tracking array
                        const index = window.xPanoramaObservers.indexOf(mainObserverId);
                        if (index > -1) {
                            window.xPanoramaObservers.splice(index, 1);
                        }
                    }
                    
                    let currentActiveScene = null; 
                    let rebuildTimeout = null;
                    let classChangeTimeout = null; 
                    
                    const observerId = `Main-${viewerId}`;
                    window.xPanoramaObservers.push(observerId);
                    const observer = new MutationObserver((mutations) => {
                        mutations.forEach((mutation) => {
                            const target = mutation.target;
                            
                            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                                // Clear any existing timeout to debounce rapid class changes
                                if (classChangeTimeout) {
                                    clearTimeout(classChangeTimeout);
                                }
                                
                                // Check if is-active-element class was added
                                const hasActiveClass = target.classList.contains('is-active-element');
                                const hadActiveClass = mutation.oldValue && mutation.oldValue.includes('is-active-element');
                                
                                // Only proceed if this is a meaningful scene activation change AND it's a different scene
                                if (target.classList.contains('brxe-xpanoramascene') && hasActiveClass && !hadActiveClass && target !== currentActiveScene) {
                                    currentActiveScene = target;
                                    
                                    // Get the scene ID to load
                                    const sceneConfigAttr = target.getAttribute('data-panorama-config');
                                    if (sceneConfigAttr) {
                                        try {
                                            const sceneConfig = JSON.parse(sceneConfigAttr);
                                            const sceneId = sceneConfig.sceneId;
                                            
                                            if (sceneId) {
                                                if (isBuilderEnv) {
                                                    viewer.dataset.preservedActiveScene = sceneId;

                                                }
                                                // Throttle rebuilds to prevent too many rapid recreations
                                                if (rebuildTimeout) {
                                                    clearTimeout(rebuildTimeout);
                                                }
                                                
                                                rebuildTimeout = setTimeout(() => {
                                                    const canvas = viewer.querySelector('canvas');
                                                    if (canvas) {
                                                        const webglContext = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
                                                        if (webglContext) {
                                                            const loseContext = webglContext.getExtension('WEBGL_lose_context');
                                                            if (loseContext) {
                                                                loseContext.loseContext();
                                                            }
                                                        }
                                                    }
                                                    instance.destroy();
                                                
                                                // Rebuild scenes from DOM
                                                const scenes = {};
                                                childScenes.forEach((sceneEl, idx) => {
                                                    const cfg = sceneEl.getAttribute('data-panorama-config');
                                                    if (cfg) {
                                                        try {
                                                            const sc = JSON.parse(cfg);
                                                            if (sc.sceneId) {
                                                                scenes[sc.sceneId] = sc;
                                                            }
                                                        } catch (e) {}
                                                    }
                                                });
                                                
                                                // Get parent viewer config
                                                const parentConfigAttr = viewerRoot.getAttribute('data-panorama-config');
                                                const parentConfig = parentConfigAttr ? JSON.parse(parentConfigAttr) : {};
                                                
                                                // Debug scene hfov values before inheritance
                                                Object.keys(scenes).forEach(sceneKey => {
                                                });
                                                
                                                // Build new tour config
                                                const newConfig = {
                                                    default: {
                                                        firstScene: sceneId,
                                                        sceneFadeDuration: 1000,
                                                        autoLoad: true,
                                                        showZoomCtrl: parentConfig.showZoomCtrl !== undefined ? parentConfig.showZoomCtrl : true,
                                                        showFullscreenCtrl: parentConfig.showFullscreenCtrl !== undefined ? parentConfig.showFullscreenCtrl : true,
                                                        mouseZoom: parentConfig.mouseZoom !== undefined ? parentConfig.mouseZoom : true,
                                                        mouseZoomSensitivity: parentConfig.mouseZoomSensitivity !== undefined ? parseFloat(parentConfig.mouseZoomSensitivity) : 1,
                                                        friction: parentConfig.friction !== undefined ? parseFloat(parentConfig.friction) : 0.5,
                                                        draggable: parentConfig.draggable !== undefined ? parentConfig.draggable : true,
                                                        disableKeyboardCtrl: parentConfig.disableKeyboardCtrl !== undefined ? parentConfig.disableKeyboardCtrl : false,
                                                        hotSpotDebug: parentConfig.hotSpotDebug !== undefined ? parentConfig.hotSpotDebug : false,
                                                        autoRotate: parentConfig.autoRotate !== undefined ? parseFloat(parentConfig.autoRotate) : undefined,
                                                    },
                                                    scenes: scenes
                                                };
                                                
                                                // Add DOM hotspots from nested elements (same as main initialization)
                                                childScenes.forEach((sceneEl) => {
                                                    const sceneConfigAttr = sceneEl.getAttribute('data-panorama-config');
                                                    if (!sceneConfigAttr) return;
                                                    
                                                    try {
                                                        const sceneConfig = JSON.parse(sceneConfigAttr);
                                                        const sceneId = sceneConfig.sceneId;
                                                        
                                                        if (!sceneId || !scenes[sceneId]) return;
                                                        
                                                        // Process DOM hotspots from .x-hotspot-data container
                                                        const domHotspots = processDOMHotspots(sceneEl, sceneId, newConfig);
                                                        if (domHotspots.length > 0) {
                                                            // Initialize hotSpots array if it doesn't exist
                                                            if (!scenes[sceneId].hotSpots) {
                                                                scenes[sceneId].hotSpots = [];
                                                            }
                                                            // Add DOM hotspots to the scene
                                                            scenes[sceneId].hotSpots = scenes[sceneId].hotSpots.concat(domHotspots);
                                                        }
                                                    } catch (e) {
                                                        console.error('Error processing scene hotspots in mutation observer:', e);
                                                    }
                                                });
                                                
                                                // Apply viewer's initial view settings to all scenes if not set on individual scenes
                                                Object.keys(scenes).forEach(sceneId => {
                                                    const scene = scenes[sceneId];
                                                    if (scene) {
                                                        if (scene.yaw === undefined && parentConfig.yaw !== undefined) {
                                                            scene.yaw = parseFloat(parentConfig.yaw);
                                                        }
                                                        if (scene.pitch === undefined && parentConfig.pitch !== undefined) {
                                                            scene.pitch = parseFloat(parentConfig.pitch);
                                                        }
                                                        if (scene.hfov === undefined && parentConfig.hfov !== undefined) {
                                                            scene.hfov = parseFloat(parentConfig.hfov);
                                                        }
                                                        if (scene.autoRotate === undefined && parentConfig.autoRotate !== undefined) {
                                                            scene.autoRotate = parseFloat(parentConfig.autoRotate);
                                                        }
                                                    }
                                                });
                                                
                                                // Check for saved view state and inject into config before creating viewer
                                                if (viewerId && sceneId) {
                                                    try {
                                                        const savedView = localStorage.getItem(`panorama_view_${viewerId}_${sceneId}`);
                                                        if (savedView) {
                                                            const viewState = JSON.parse(savedView);
                                                            // Only use if saved within last 5 minutes
                                                            if (Date.now() - viewState.timestamp < 300000) {
                                                                // Inject saved values into the scene config
                                                                if (newConfig.scenes && newConfig.scenes[sceneId]) {
                                                                    newConfig.scenes[sceneId].yaw = viewState.yaw;
                                                                    newConfig.scenes[sceneId].pitch = viewState.pitch;
                                                                    newConfig.scenes[sceneId].hfov = viewState.hfov;
                                                                            }
                                                            }
                                                        }
                                                    } catch (e) {
                                                        console.warn('Could not inject saved view state:', e);
                                                    }
                                                }
                                                
                                                // Recreate viewer
                                                const newInstance = pannellum.viewer(viewer, newConfig);
                                                viewer.pannellumInstance = newInstance;
                                                
                                                // Save view state on animation finished (builder only)
                                                if (viewerId) {
                                                    let saveEnabled = false;
                                                    
                                                    // Enable saving after initial load is complete
                                                    newInstance.on('load', () => {
                                                        setTimeout(() => {
                                                            saveEnabled = true;
                                                        }, 1000); // Wait 1 second after load before enabling saves
                                                    });
                                                    
                                                    newInstance.on('animatefinished', () => {
                                                        if (!saveEnabled) {
                                                            return;
                                                        }
                                                        
                                                        try {
                                                            // Get current scene ID
                                                            let currentSceneId = null;
                                                            if (newConfig.scenes) {
                                                                currentSceneId = newInstance.getScene();
                                                            } else {
                                                                currentSceneId = 'default';
                                                            }
                                                            
                                                            if (currentSceneId) {
                                                                const currentView = {
                                                                    yaw: newInstance.getYaw(),
                                                                    pitch: newInstance.getPitch(),
                                                                    hfov: newInstance.getHfov(),
                                                                    timestamp: Date.now()
                                                                };
                                                                localStorage.setItem(`panorama_view_${viewerId}_${currentSceneId}`, JSON.stringify(currentView));
                                                            }
                                                        } catch (e) {
                                                            console.warn('Could not save view state:', e);
                                                        }
                                                    });
                                                }
                                                
                                                newInstance.on('load', () => {
                                                    viewerRoot.setAttribute('data-panorama-loaded', 'true');
                                                });
                                                
                                                // Update global instances object
                                                if (!window.xPanorama) {
                                                    window.xPanorama = {};
                                                }
                                                if (!window.xPanorama.Instances) {
                                                    window.xPanorama.Instances = {};
                                                }
                                                if (viewerId) {
                                                    window.xPanorama.Instances[viewerId] = newInstance;
                                                }
                                                
                                                // Recreate debug overlay
                                                createDebugOverlay(newInstance, newConfig);
                                                }, 150); // End throttle timeout
                                            }
                                        } catch (e) {
                                            console.error('Error loading scene:', e);
                                        }
                                    }
                                }
                            } else if (mutation.type === 'attributes' && mutation.attributeName === 'data-panorama-config') {
                                // Get scene IDs for cleaner logging
                                const targetSceneConfig = JSON.parse(target.getAttribute('data-panorama-config'));
                                
                                // Find the scene that actually has is-active-element class
                                const realActiveScene = viewerRoot.querySelector('.brxe-xpanoramascene.is-active-element');
                                const realActiveSceneConfig = realActiveScene ? JSON.parse(realActiveScene.getAttribute('data-panorama-config')) : null;
                                // Config changed - rebuild viewer (any scene config change should trigger rebuild)
                                if (currentActiveScene) {
                                    const sceneConfigAttr = target.getAttribute('data-panorama-config');
                                    if (sceneConfigAttr) {
                                        // Throttle rebuilds to prevent too many rapid recreations
                                        if (rebuildTimeout) {
                                            clearTimeout(rebuildTimeout);
                                        }
                                        
                                        rebuildTimeout = setTimeout(() => {
                                            
                                            try {
                                                // Find the actually active scene (same logic as Observer 1)
                                                const realActiveScene = viewerRoot.querySelector('.brxe-xpanoramascene.is-active-element');
                                                if (!realActiveScene) return;
                                                
                                                const activeSceneConfigAttr = realActiveScene.getAttribute('data-panorama-config');
                                                const activeSceneConfig = JSON.parse(activeSceneConfigAttr);
                                                const sceneId = activeSceneConfig.sceneId;
                                                
                                                if (sceneId) {
                                                    
                                                    // Destroy and recreate the entire viewer with fresh config from DOM
                                                    instance.destroy();
                                                    
                                                    // Rebuild scenes from DOM (fetch fresh DOM elements)
                                                    const scenes = {};
                                                    const freshChildScenes = viewerRoot.querySelectorAll('.brxe-xpanoramascene');
                                                    freshChildScenes.forEach((sceneEl, idx) => {
                                                        const cfg = sceneEl.getAttribute('data-panorama-config');
                                                        if (cfg) {
                                                            try {
                                                                const sc = JSON.parse(cfg);
                                                                if (sc.sceneId) {
                                                                    scenes[sc.sceneId] = sc;
                                                                }
                                                            } catch (e) {}
                                                        }
                                                    });
                                                    
                                                    // Get parent viewer config
                                                    const parentConfigAttr = viewerRoot.getAttribute('data-panorama-config');
                                                    const parentConfig = parentConfigAttr ? JSON.parse(parentConfigAttr) : {};
                                                    
                                                    // Build new tour config
                                                    const newConfig = {
                                                        default: {
                                                            firstScene: sceneId,
                                                            sceneFadeDuration: 1000,
                                                            autoLoad: parentConfig.autoLoad !== undefined ? parentConfig.autoLoad : true,
                                                            showZoomCtrl: parentConfig.showZoomCtrl !== undefined ? parentConfig.showZoomCtrl : true,
                                                            showFullscreenCtrl: parentConfig.showFullscreenCtrl !== undefined ? parentConfig.showFullscreenCtrl : true,
                                                            mouseZoom: parentConfig.mouseZoom !== undefined ? parentConfig.mouseZoom : true,
                                                            mouseZoomSensitivity: parentConfig.mouseZoomSensitivity !== undefined ? parseFloat(parentConfig.mouseZoomSensitivity) : 1,
                                                            friction: parentConfig.friction !== undefined ? parseFloat(parentConfig.friction) : 0.5,
                                                            draggable: parentConfig.draggable !== undefined ? parentConfig.draggable : true,
                                                            disableKeyboardCtrl: parentConfig.disableKeyboardCtrl !== undefined ? parentConfig.disableKeyboardCtrl : false,
                                                            hotSpotDebug: parentConfig.hotSpotDebug !== undefined ? parentConfig.hotSpotDebug : false,
                                                            autoRotate: parentConfig.autoRotate !== undefined ? parseFloat(parentConfig.autoRotate) : undefined,
                                                        },
                                                        scenes: scenes
                                                    }; 
                                                    
                                                    // Add DOM hotspots from nested elements (only for active scene, using fresh DOM)
                                                    freshChildScenes.forEach((sceneEl) => {
                                                        // Only process the active scene
                                                        if (!sceneEl.classList.contains('is-active-element')) {
                                                            return;
                                                        }
                                                        
                                                        const sceneConfigAttr = sceneEl.getAttribute('data-panorama-config');
                                                        if (!sceneConfigAttr) return;
                                                        
                                                        try {
                                                            const sceneConfig = JSON.parse(sceneConfigAttr);
                                                            const sceneId = sceneConfig.sceneId;
                                                            
                                                            if (!sceneId || !scenes[sceneId]) return;

                                                            
                                                            // Process DOM hotspots from .x-hotspot-data container 
                                                            const domHotspots = processDOMHotspots(sceneEl, sceneId, newConfig);
                                                            if (domHotspots.length > 0) {

                                                                // Initialize hotSpots array if it doesn't exist
                                                                if (!scenes[sceneId].hotSpots) {
                                                                    scenes[sceneId].hotSpots = [];
                                                                }
                                                                // Add DOM hotspots to the scene
                                                                scenes[sceneId].hotSpots = scenes[sceneId].hotSpots.concat(domHotspots);
                                                            }
                                                        } catch (e) {
                                                            console.error('Error processing scene hotspots in mutation observer 2:', e);
                                                        }
                                                    });
                                                    
                                                    // Apply viewer's initial view settings to all scenes if not set on individual scenes
                                                    Object.keys(scenes).forEach(sceneId => {
                                                        const scene = scenes[sceneId];
                                                        if (scene) {
                                                            if (scene.yaw === undefined && parentConfig.yaw !== undefined) {
                                                                scene.yaw = parseFloat(parentConfig.yaw);
                                                            }
                                                            if (scene.pitch === undefined && parentConfig.pitch !== undefined) {
                                                                scene.pitch = parseFloat(parentConfig.pitch);
                                                            }
                                                            if (scene.hfov === undefined && parentConfig.hfov !== undefined) {
                                                                scene.hfov = parseFloat(parentConfig.hfov);
                                                            }
                                                            if (scene.autoRotate === undefined && parentConfig.autoRotate !== undefined) {
                                                                scene.autoRotate = parseFloat(parentConfig.autoRotate);
                                                            }
                                                        } 
                                                    });
                                                    
                                                    // Check for saved view state and inject into fresh config
                                                    if (viewerId && sceneId) {
                                                        try {
                                                            const savedView = localStorage.getItem(`panorama_view_${viewerId}_${sceneId}`);
                                                            if (savedView) {
                                                                const viewState = JSON.parse(savedView);
                                                                // Only use if saved within last 5 minutes
                                                                const age = Date.now() - viewState.timestamp;
                                                                if (age < 300000) {
                                                                    // Inject saved values into the scene config (this is fresh from DOM)
                                                                    if (newConfig.scenes && newConfig.scenes[sceneId]) {
                                                                        
                                                                        newConfig.scenes[sceneId].yaw = viewState.yaw;
                                                                        newConfig.scenes[sceneId].pitch = viewState.pitch;
                                                                        newConfig.scenes[sceneId].hfov = viewState.hfov;
                                                                        
                                                                    } else {
                                                                        
                                                                    }
                                                                } else {
                                                                    
                                                                } 
                                                            } else { 
                                                                
                                                            }
                                                        } catch (e) {
                                                            console.warn('Could not inject saved view state:', e);
                                                        }
                                                    }
                                                    
                                                    // Recreate viewer
                                                    const newInstance = pannellum.viewer(viewer, newConfig);
                                                    viewer.pannellumInstance = newInstance;
                                                    
                                                    // Save view state on animation finished (builder only)
                                                    if (viewerId) {
                                                        let saveEnabled = false;
                                                        
                                                        // Enable saving after initial load is complete
                                                        newInstance.on('load', () => {
                                                            setTimeout(() => {
                                                                saveEnabled = true;
                                                            }, 1000); // Wait 1 second after load before enabling saves
                                                        });
                                                        
                                                        newInstance.on('animatefinished', () => {
                                                            if (!saveEnabled) {
                                                                return;
                                                            }
                                                            
                                                            try {
                                                                // Get current scene ID
                                                                let currentSceneId = null;
                                                                if (newConfig.scenes) {
                                                                    currentSceneId = newInstance.getScene();
                                                                } else {
                                                                    currentSceneId = 'default';
                                                                }
                                                                
                                                                if (currentSceneId) {
                                                                    const currentView = {
                                                                        yaw: newInstance.getYaw(),
                                                                        pitch: newInstance.getPitch(),
                                                                        hfov: newInstance.getHfov(),
                                                                        timestamp: Date.now()
                                                                    };
                                                                    localStorage.setItem(`panorama_view_${viewerId}_${currentSceneId}`, JSON.stringify(currentView));
                                                                }
                                                            } catch (e) {
                                                                console.warn('Could not save view state:', e);
                                                            }
                                                        });
                                                    }
                                                    
                                                    
                                                    // Hide the inner content div when scene loads
                                                    newInstance.on('load', () => {
                                                        viewerRoot.setAttribute('data-panorama-loaded', 'true');
                                                    });
                                                    
                                                    // Update global instances object
                                                    if (!window.xPanorama) {
                                                        window.xPanorama = {};
                                                    }
                                                    if (!window.xPanorama.Instances) {
                                                        window.xPanorama.Instances = {};
                                                    }
                                                    if (viewerId) {
                                                        window.xPanorama.Instances[viewerId] = newInstance;
                                                    }
                                                    
                                                    // Recreate debug overlay
                                                    createDebugOverlay(newInstance, newConfig);
                                                } 
                                            } catch (e) {
                                                console.error('Error reloading scene:', e);
                                            }
                                        }, 0); // End throttle timeout
                                    }
                                }
                            }
                        });
                    });
                    
                    // Observe all child scenes for class and config changes
                    childScenes.forEach((sceneEl) => {
                        observer.observe(sceneEl, { 
                            attributes: true, 
                            attributeFilter: ['class', 'data-panorama-config'],
                            attributeOldValue: true
                        });
                    });
                    
                    // Store observer reference globally to survive DOM changes
                    window.xPanoramaObserverInstances[mainObserverId] = observer;
                }
                
                // Create debug overlay
                createDebugOverlay(instance, config);
                
                // Prevent Shift key from triggering zoom in Pannellum
                viewer.addEventListener('keydown', function(e) {
                    if (e.key === 'Shift') {
                        e.stopPropagation();
                    }
                }, true); 
                
                
                // Require Command/Ctrl key for mouse wheel zoom (like maps)
                const mouseZoomEnabled = config.mouseZoom !== false;
                let scrollOverlayTimeout = null; // Track timeout to clear it
                
                if (mouseZoomEnabled) {
                    // Intercept wheel events on the viewer container (desktop)
                    viewer.addEventListener('wheel', (e) => {
                        // Check if we're in fullscreen mode (native or iOS fallback)
                        const isFullscreen = document.fullscreenElement || 
                                           document.webkitFullscreenElement || 
                                           viewerRoot.hasAttribute('data-x-fullscreen-fallback-active');
                        
                        // In fullscreen mode, allow wheel zoom without modifier keys
                        if (isFullscreen) {
                            return; // Let Pannellum handle the wheel event normally
                        }
                        
                        // In normal mode, only allow zoom if Command (Mac) or Ctrl (Windows/Linux) is pressed
                        if (!e.metaKey && !e.ctrlKey) {
                            // Don't stop propagation - let the page scroll
                            // But prevent Pannellum from zooming
                            e.stopPropagation();
                            
                            // Show scroll instructions overlay if it exists
                            if (scrollOverlay) {
                                scrollOverlay.style.opacity = '1'; 
                                
                                // Clear any existing timeout
                                if (scrollOverlayTimeout) {
                                    clearTimeout(scrollOverlayTimeout);
                                }
                                
                                // Auto-hide after 1 second of no scrolling
                                scrollOverlayTimeout = setTimeout(() => {
                                    if (scrollOverlay) {
                                        scrollOverlay.style.opacity = '0';
                                    }
                                    scrollOverlayTimeout = null;
                                }, 500);
                            }
                        } else {
                            // User is using correct modifier keys - hide overlay immediately
                            if (scrollOverlay) {
                                scrollOverlay.style.opacity = '0';
                                // Clear timeout since we're hiding immediately
                                if (scrollOverlayTimeout) {
                                    clearTimeout(scrollOverlayTimeout);
                                    scrollOverlayTimeout = null;
                                }
                            }
                        }
                    }, { capture: true });
                    
                    // Handle touch events for mobile (require two fingers for zoom)
                    let touchCount = 0;
                    
                    viewer.addEventListener('touchstart', (e) => {
                        touchCount = e.touches.length;
                    }, { capture: true });
                    
                    viewer.addEventListener('touchmove', (e) => {
                        const isFullscreen = document.fullscreenElement || 
                                           document.webkitFullscreenElement || 
                                           viewerRoot.hasAttribute('data-x-fullscreen-fallback-active');
                        
                        // If only one finger and NOT in fullscreen, prevent panorama interaction to allow page scroll
                        if (e.touches.length === 1 && touchCount === 1 && !isFullscreen) {
                            e.stopPropagation();
                            
                            // Show mobile instructions using the same overlay
                            if (scrollOverlay) {
                                const mobileText = originalConfig.scrollInstructionsMobileText || 'Use two fingers to navigate';
                                scrollOverlay.innerHTML = mobileText;
                                scrollOverlay.style.opacity = '1';
                                
                                // Clear any existing timeout
                                if (scrollOverlayTimeout) {
                                    clearTimeout(scrollOverlayTimeout);
                                }
                                
                                // Auto-hide after 500ms (same as desktop)
                                scrollOverlayTimeout = setTimeout(() => {
                                    if (scrollOverlay) {
                                        scrollOverlay.style.opacity = '0';
                                    }
                                    scrollOverlayTimeout = null;
                                }, 500);
                            }
                        } else if (e.touches.length >= 2 && !isFullscreen) {
                            // User is using two fingers - hide overlay immediately
                            if (scrollOverlay) {
                                scrollOverlay.style.opacity = '0';
                                // Clear timeout since we're hiding immediately
                                if (scrollOverlayTimeout) {
                                    clearTimeout(scrollOverlayTimeout);
                                    scrollOverlayTimeout = null;
                                }
                            }
                        }
                    }, { capture: true });
                    
                    viewer.addEventListener('touchend', (e) => {
                        touchCount = e.touches.length;
                    }, { capture: true });
                }


                
                // iOS Fullscreen fallback (like media player) - iPhone only, iPad supports native fullscreen
                const isIPhone = /iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
                const fullscreenFallbackEnabled = config.fullscreenFallback !== false;
                const showFullscreenCtrl = config.default ? config.default.showFullscreenCtrl : config.showFullscreenCtrl;
                
                if (isIPhone && fullscreenFallbackEnabled && showFullscreenCtrl !== false) {
                    let scrollPosition = 0;
                    
                    function enterFullscreenFallback() {
                        scrollPosition = window.scrollY;
                        document.body.style.position = 'fixed';
                        document.body.style.top = '-' + scrollPosition + 'px';
                        document.body.style.width = '100%';
                        viewerRoot.setAttribute('data-x-fullscreen-fallback-active', '');
                        viewerRoot.dispatchEvent(new Event('x_panorama_fullscreen_active'));
                        
                        // Update button state
                        const fullscreenBtn = viewer.querySelector('.pnlm-fullscreen-toggle-button, .x-panorama-fullscreen-btn');
                        if (fullscreenBtn) {
                            fullscreenBtn.classList.remove('pnlm-fullscreen-toggle-button-inactive');
                            fullscreenBtn.classList.add('pnlm-fullscreen-toggle-button-active');
                        }
                        
                        // Trigger resize after fullscreen transition
                        setTimeout(() => {
                            if (viewer.pannellumInstance && viewer.pannellumInstance.resize) {
                                viewer.pannellumInstance.resize();
                            }
                        }, 0);
                    }
                    
                    function exitFullscreenFallback() {
                        document.body.style.position = '';
                        document.body.style.top = '';
                        document.body.style.width = '';
                        viewerRoot.removeAttribute('data-x-fullscreen-fallback-active');
                        viewerRoot.dispatchEvent(new Event('x_panorama_fullscreen_inactive'));
                        window.scrollTo({
                            top: scrollPosition,
                            behavior: 'instant'
                        });
                        
                        // Update button state
                        const fullscreenBtn = viewer.querySelector('.pnlm-fullscreen-toggle-button, .x-panorama-fullscreen-btn');
                        if (fullscreenBtn) {
                            fullscreenBtn.classList.remove('pnlm-fullscreen-toggle-button-active');
                            fullscreenBtn.classList.add('pnlm-fullscreen-toggle-button-inactive');
                        }
                        
                        // Trigger resize after exiting fullscreen
                        setTimeout(() => {
                            if (viewer.pannellumInstance && viewer.pannellumInstance.resize) {
                                viewer.pannellumInstance.resize();
                            }
                        }, 0);
                    }
                    
                    // Pannellum doesn't render fullscreen button on iPhone, so we need to create one
                    // Only if showFullscreenCtrl is not explicitly disabled
                    if (showFullscreenCtrl !== false) {
                        setTimeout(() => {
                            let fullscreenBtn = viewer.querySelector('.pnlm-fullscreen-toggle-button');
                            
                            if (!fullscreenBtn) {
                                // Create custom fullscreen button
                                const controls = viewer.querySelector('.pnlm-controls-container');
                                if (controls) {
                                    fullscreenBtn = document.createElement('div');
                                    fullscreenBtn.className = 'pnlm-fullscreen-toggle-button pnlm-sprite pnlm-fullscreen-toggle-button-inactive pnlm-controls pnlm-control x-panorama-fullscreen-btn';
                                    fullscreenBtn.setAttribute('role', 'button');
                                    fullscreenBtn.setAttribute('aria-label', 'Toggle fullscreen');
                                    controls.appendChild(fullscreenBtn);
                                }
                            }
                            
                            // Listen for fullscreen button clicks
                            if (fullscreenBtn) {
                                fullscreenBtn.addEventListener('click', (e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    if (viewerRoot.hasAttribute('data-x-fullscreen-fallback-active')) {
                                        exitFullscreenFallback();
                                    } else {
                                        enterFullscreenFallback();
                                    }
                                });
                            }
                        }, 100);
                    }
                }
                
                // Store viewer instance on element for potential API access
                viewer.pannellumInstance = instance;

                // Store in global instances object
                if (!window.xPanorama) {
                    window.xPanorama = {};
                }
                if (!window.xPanorama.Instances) {
                    window.xPanorama.Instances = {};
                }
                if (viewerId) {
                    window.xPanorama.Instances[viewerId] = instance;
                }

            } catch (e) {
                console.error('Error initializing panorama viewer:', e);
            }
            
            }; // End initViewer function
            
            // Handle loading strategy
            const loadingStrategy = config.loadingStrategy || 'eager';
            
            // Store initViewer function for manual initialization
            viewerRoot.xPanoramaInit = initViewer;
            
            // In builder, always load immediately
            if (isBuilderEnv) {
                setTimeout(initViewer, 100);
            } else if (loadingStrategy === 'interactions') {
                // Wait for manual initialization via interactions or custom code
                // Show placeholder image if provided
                const placeholder = config.placeholderImage;
                if (placeholder) {
                    viewer.style.backgroundImage = `url(${placeholder})`;
                    viewer.style.backgroundSize = 'cover';
                    viewer.style.backgroundPosition = 'center';
                }
                // User can call element.xPanoramaInit() or window.xPanoramaInit(element)
                return;
            } else if (loadingStrategy === 'lazy') {
                // Lazy load - use Intersection Observer
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            initViewer();
                            observer.unobserve(viewerRoot);
                        }
                    });
                }, { rootMargin: '50px' });
                observer.observe(viewerRoot);
            } else if (loadingStrategy === 'click') {
                // Click to load - show placeholder, user can nest their own buttons
                const placeholder = config.placeholderImage;
                if (placeholder) {
                    viewer.style.backgroundImage = `url(${placeholder})`;
                    viewer.style.backgroundSize = 'cover';
                    viewer.style.backgroundPosition = 'center';
                }
                
                // Add click listener to the inner container
                const innerContainer = viewerRoot.querySelector('.x-panorama-viewer_inner');
                if (innerContainer) {
                    const clickHandler = (e) => {
                        innerContainer.removeEventListener('click', clickHandler);
                        // Clear background image
                        viewer.style.backgroundImage = '';
                        // Initialize the viewer
                        initViewer();
                        if (!isBuilderEnv) {
                            //innerContainer.remove();
                        }
                    };
                    
                    innerContainer.addEventListener('click', clickHandler);
                }
                
                return;
            } else {
                // Eager - load immediately
                initViewer();
            }

        })

    }

    extrasPanoramaViewer(document)

    const xPanoramaViewerAJAX = xExtrasRegisterAJAXHandler('doExtrasPanoramaViewer');

    // Expose function
    window.doExtrasPanoramaViewer = extrasPanoramaViewer;

    // Global helper function for manual initialization
    window.xPanoramaInit = function(element) {
        if (!element) {
            return false;
        }
        
        // Find the viewer root element
        let viewerRoot = element;
        if (!viewerRoot.classList.contains('brxe-xpanoramaviewer')) {
            viewerRoot = element.closest('.brxe-xpanoramaviewer');
        }
        
        if (!viewerRoot) {
            return false;
        }
        
        // Check if init function exists
        if (typeof viewerRoot.xPanoramaInit === 'function') {
            viewerRoot.xPanoramaInit();
            return true;
        } else {
            return false;
        }
    };

    // Expose via bricksextras object for Bricks interactions
    if (typeof bricksextras === 'undefined') {
        window.bricksextras = {};
    }
    
    bricksextras.panorama = {
        load: (brxParam) => {
            let target = brxParam?.target || false;
            if (target) {
                return window.xPanoramaInit(target);
            }
            return false;
        }
    };

}

document.addEventListener("DOMContentLoaded",function(e){
    
    if ( !bricksIsFrontend ) {
        return;
    }

    xPanoramaViewer()
})
