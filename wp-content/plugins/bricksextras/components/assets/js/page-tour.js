function xPageTour() {

    if ( document.querySelector('body > .brx-body.iframe') ) {
        return
    }
    
    // Helper functions for tour state persistence
    const tourStorage = {
        // Storage key for tour state
        storageKey: 'x_page_tour_state',
        
        // Save tour state to localStorage
        saveTourState: function(tourId, stepIndex) {
            if (!window.localStorage) return false;
            
            try {
                const state = {
                    tourId: tourId,
                    stepIndex: stepIndex,
                    timestamp: Date.now()
                };
                
                window.localStorage.setItem(this.storageKey, JSON.stringify(state));
                return true;
            } catch (e) {
                return false;
            }
        },
        
        // Get tour state from localStorage
        getTourState: function() {
            if (!window.localStorage) return null;
            
            try {
                const stateStr = window.localStorage.getItem(this.storageKey);
                if (!stateStr) return null;
                
                return JSON.parse(stateStr);
            } catch (e) {
                return null;
            }
        },
        
        // Clear tour state from localStorage
        clearTourState: function() {
            if (!window.localStorage) return false;
            
            try {
                window.localStorage.removeItem(this.storageKey);
                return true;
            } catch (e) {
                return false;
            }
        },
        
        // Save seen state to localStorage
        saveCompletionState: function(tourId) {
            if (!window.localStorage) return false;
            
            try {
                const key = `x_page_tour_seen_${tourId}`;
                window.localStorage.setItem(key, Date.now());
                return true;
            } catch (e) {
                return false;
            }
        },
        
        // Save completed state to localStorage
        saveCompletedState: function(tourId) {
            if (!window.localStorage) return false;
            
            try {
                const key = `x_page_tour_completed_${tourId}`;
                window.localStorage.setItem(key, Date.now());
                return true;
            } catch (e) {
                return false;
            }
        },
        
        // Check if tour has been completed
        isTourCompleted: function(tourId) {
            if (!window.localStorage) return false;
            
            try {
                const key = `x_page_tour_completed_${tourId}`;
                return window.localStorage.getItem(key) !== null;
            } catch (e) {
                return false;
            }
        },
        
        // Check if tour should be shown again based on configuration
        shouldShowTour: function(tourId, showAgainConfig, showAgainDays, showAgainHours) {
            if (!window.localStorage) return true;
            
            try {
                // If manual, don't auto-show
                if (showAgainConfig === 'manual') {
                    return false;
                }
                
                // If show on every visit, always return true
                if (showAgainConfig === 'page_load') {
                    return true;
                }
                
                const key = `x_page_tour_seen_${tourId}`;
                const lastSeenStr = window.localStorage.getItem(key);
                
                // If never seen before, show it
                if (!lastSeenStr) {
                    return true;
                }
                
                // If never show again, don't show it
                if (showAgainConfig === 'never') {
                    return false;
                }
                
                // If show again until completed, check if it's been completed
                if (showAgainConfig === 'until_complete') {
                    // If the tour has been completed, don't show it again
                    if (this.isTourCompleted(tourId)) {
                        return false;
                    }
                    // Otherwise, show it again
                    return true;
                }
                
                // If show after specific time
                if (showAgainConfig === 'after') {
                    const lastSeen = parseInt(lastSeenStr, 10);
                    const now = Date.now();
                    
                    // Calculate total milliseconds to wait
                    const daysMs = (showAgainDays || 0) * 24 * 60 * 60 * 1000;
                    const hoursMs = (showAgainHours || 0) * 60 * 60 * 1000;
                    const waitTime = daysMs + hoursMs;
                    
                    // If enough time has passed, show it again
                    return (now - lastSeen) > waitTime;
                }
                
                return true;
            } catch (e) {
                return true;
            }
        }
    };
    
    // Helper function to find elements within component scope
    const findElementInScope = function(selector, componentId, tourId) {
        if (!selector) return null;
        
        // If we have a component ID, find the element within that component
        if (componentId) {
            // Find the component element
            const componentElement = document.querySelector(`.brxe-${componentId}`);
            if (componentElement) {
                // Search within the component element
                const foundElement = componentElement.querySelector(selector);
                
                return foundElement;
            }
        }
        
        // If no component ID or component not found, search in the entire document
        const globalElement = document.querySelector(selector);
        return globalElement;
    };
    
    // Initialize page tours
    const initPageTours = function(container) {
        
        container.querySelectorAll('.x-page-tour').forEach((element) => {
            
            if (element.hasAttribute('data-x-tour-initialized')) {
                return;
            }
            
            // Get tour configuration
            const tourConfig = JSON.parse(element.getAttribute('data-x-page-tour'));
            
            // Check if remember progress is enabled
            const rememberProgress = tourConfig.rememberProgress === true;
            
            // Check for template elements with class 'x-page-tour-step' to use as steps
            const tourStepTemplates = element.querySelectorAll('template.x-page-tour-step');
            
            if (tourStepTemplates.length > 0) {
                tourConfig.steps = Array.from(tourStepTemplates).map((template) => {
                    const stepData = {
                        title: template.getAttribute('data-step-title') || "",
                        selector: template.getAttribute('data-step-selector') || "",
                        position: template.getAttribute('data-step-position') || "auto",
                        highlightElement: template.hasAttribute('data-step-highlight'),
                        scrollTo: template.hasAttribute('data-step-scroll'),
                        text: template.innerHTML
                    };
                    
                    if (template.hasAttribute('data-step-advanced')) {
                        try {
                            stepData.advancedOptions = JSON.parse(template.getAttribute('data-step-advanced'));
                        } catch (e) {
                        }
                    }
                    
                    return stepData;
                });
            }
            
            // Check for nested elements with class 'brxe-block' to use as steps
            const nestedSteps = element.querySelectorAll('.brxe-xpagetourstep');
            
            // If we have nested elements, use them as steps
            if (nestedSteps.length > 0) {
                tourConfig.steps = [];
                
                nestedSteps.forEach((stepElement, index) => {
                    
                    // Use the element's HTML content as the step text
                    const stepConfig = {
                        title: '',
                        text: stepElement.innerHTML,
                        id: `step-${index}`
                    };
                    
                    // Get optional attributes
                    if (stepElement.hasAttribute('data-step-selector')) {
                        stepConfig.selector = stepElement.getAttribute('data-step-selector');
                    }
                    
                    if (stepElement.hasAttribute('data-step-position')) {
                        stepConfig.position = stepElement.getAttribute('data-step-position');
                    }
                    
                    if (stepElement.hasAttribute('data-step-highlight')) {
                        stepConfig.highlightElement = stepElement.getAttribute('data-step-highlight') === 'true';
                    }
                    
                    if (stepElement.hasAttribute('data-step-scroll')) {
                        stepConfig.scrollTo = stepElement.getAttribute('data-step-scroll') === 'true';
                    }
                    
                    // Add custom class if specified
                    if (stepElement.hasAttribute('data-step-class')) {
                        stepConfig.customClass = stepElement.getAttribute('data-step-class');
                    }
                    
                    // Parse advanced options if available
                    if (stepElement.hasAttribute('data-step-advanced')) {
                        try {
                            const advancedOptions = JSON.parse(stepElement.getAttribute('data-step-advanced'));
                            stepConfig.advancedOptions = advancedOptions;
                        } catch (e) {
                        }
                    }
                    
                    tourConfig.steps.push(stepConfig);
                });
                
                // Hide the original elements
                nestedSteps.forEach(el => {
                    el.style.display = 'none';
                });
            }
            
            // Skip if no steps defined (either from config or nested elements)
            if (!tourConfig.steps || !tourConfig.steps.length) {
                return;
            }
            
            // Get element ID and classes
            const elementId = element.getAttribute('data-x-id');
            const elementIdAttr = element.getAttribute('id');
            
            const containerId = elementIdAttr ? elementIdAttr : elementId
            
            // Get container classes: combine element classes with container class
            let containerClass = 'x-page-tour-container';
            
            // Add element classes if any
            if (element.className) {
                containerClass += ' ' + element.className;
            }
            
            // Add custom container class if specified
            if (tourConfig.containerClass) {
                containerClass += ' ' + tourConfig.containerClass;
            }
            
            // Function to create container
            const createTourContainer = () => {
                // Check if container already exists in body
                let container = document.querySelector('body > #' + containerId);
                
                // If not in body, create it
                if (!container) {
                    container = document.createElement('div');
                    container.id = containerId;
                    container.className = containerClass.trim();
                    document.body.appendChild(container);
                } else {
                }
                return container;
            };
            
            // Function to remove container
            const removeTourContainer = () => {
                const container = document.getElementById(containerId);
                if (container) {
                    // Check if there are any elements other than the modal overlay
                    const hasOnlyOverlay = container.childElementCount === 1 && 
                        container.querySelector('.shepherd-modal-overlay-container');
                    
                    if (container.childElementCount === 0 || hasOnlyOverlay) {
                        container.remove();
                    }
                }
            };    
            
            // Check for saved tour state and get starting step if needed
            let startAtStep = 0;
            
            // Check if we should resume from saved state
            if (rememberProgress && elementId) {
                const savedState = tourStorage.getTourState(elementId);
                if (savedState && savedState.stepIndex !== undefined) {
                    startAtStep = savedState.stepIndex;
                    
                    // Optional: Check if state is too old (e.g., older than 24 hours)
                    const maxAge = 24 * 60 * 60 * 1000; // 24 hours in milliseconds
                    if (savedState.timestamp && (Date.now() - savedState.timestamp > maxAge)) {
                        // State is too old, don't resume
                        startAtStep = 0;
                        tourStorage.clearTourState(elementId);
                    }
                }
            }
            
            // Create Shepherd tour instance
            const tour = new Shepherd.Tour({
                useModalOverlay: tourConfig.useModalOverlay || false,
                keyboardNavigation: tourConfig.keyboardNavigation || false,
                exitOnEsc: tourConfig.exitOnEsc || false,
                confirmCancel: tourConfig.confirmCancel || false,
                confirmCancelMessage: tourConfig.confirmCancelMessage || 'Are you sure you want to exit the tour?',
                defaultStepOptions: {
                    cancelIcon: {
                        enabled: tourConfig.disableCloseButton ? false : true
                    },
                    canClickTarget: tourConfig.canClickTarget || false,
                    classes: 'extras-theme',
                    scrollTo: false
                }
            });
            
            // Add progress bar if enabled
            if (tourConfig.showProgress) {
                
                // Create progress bar element
                const progressBar = document.createElement('div');
                progressBar.className = 'shepherd-progress-bar';
                progressBar.setAttribute('role', 'progressbar');
                progressBar.setAttribute('aria-valuemin', '0');
                progressBar.setAttribute('aria-valuemax', '100');
                progressBar.setAttribute('aria-label', 'Tour progress');
                
                // Create progress bar container
                const progressContainer = document.createElement('div');
                progressContainer.className = 'shepherd-progress-container';

                if (tourConfig.progressPosition === 'top') {
                    progressContainer.style.top = '0';
                    progressContainer.style.bottom = 'auto';
                } else {
                    progressContainer.style.top = 'auto';
                    progressContainer.style.bottom = '0';
                }

                progressContainer.appendChild(progressBar);
                
                // Add progress bar to each step and update its value
                tour.on('show', (e) => {
                    // Wait for DOM to be fully rendered before accessing elements
                    setTimeout(() => {
                        if (e && e.step && e.step.el) {
                            // Get the current step index and calculate progress
                            let currentStepIndex = 0;
                            const stepId = e.step.id;
                            const matchingIndex = tour.steps.findIndex(s => s.id === stepId);
                            if (matchingIndex !== -1) {
                                currentStepIndex = matchingIndex;
                            } else {
                                currentStepIndex = parseInt(stepId.replace('step-', '')) || 0;
                            }
                            const totalSteps = tourConfig.steps.length;
                            
                            // Calculate progress - ensure first step shows progress
                            let progress;
                            if (totalSteps <= 1) {
                                progress = 100; // Single step = 100%
                            } else {
                                // For multiple steps, first step starts at (100/totalSteps)%
                                // This ensures we see some progress on the first step
                                progress = Math.max(0, Math.min(100, ((currentStepIndex + 1) / totalSteps) * 100));
                            }
                            
                            // Create accessible label text
                            const progressLabel = `Step ${currentStepIndex + 1} of ${totalSteps}`;
                            
                            // Check if progress bar already exists
                            let progressElement = e.step.el.querySelector('.shepherd-progress-container');
                            
                            // If it doesn't exist, create and append it to the step element
                            if (!progressElement) {
                                progressElement = progressContainer.cloneNode(true);
                                e.step.el.appendChild(progressElement);
                            }
                                
                            // Update the progress bar width
                            const progressBar = progressElement.querySelector('.shepherd-progress-bar');
                            if (progressBar) {
                                progressBar.style.width = `${progress}%`;
                                progressBar.setAttribute('aria-valuenow', Math.round(progress));
                                progressBar.setAttribute('aria-valuetext', progressLabel);
                            }
                        }
                    }, 0); // Increased timeout to ensure DOM is ready
                });
            }
            
            // Add steps to tour
            tourConfig.steps.forEach((step, index) => {
                const isFirstStep = index === 0;
                const isLastStep = index === tourConfig.steps.length - 1;
                
                // Prepare buttons
                const buttons = [];
                
                // Only add navigation buttons if navButtons is true or undefined (for backward compatibility)
                if (tourConfig.navButtons !== false) {
                    // Back button (except for first step)
                    if (!isFirstStep) {
                        buttons.push({
                            text: step.advancedOptions && step.advancedOptions.buttonPrevText 
                                ? step.advancedOptions.buttonPrevText 
                                : (tourConfig.buttonPrevText || 'Back'),
                            action: tour.back,
                            classes: 'x-page-tour-button x-page-tour-button__back'
                        });
                    }
                    
                    // Next/Complete button
                    if (!isLastStep) {
                        buttons.push({
                            text: step.advancedOptions && step.advancedOptions.buttonNextText 
                                ? step.advancedOptions.buttonNextText 
                                : (tourConfig.buttonNextText || 'Next'),
                            action: tour.next,
                            classes: 'x-page-tour-button x-page-tour-button__next'
                        });
                    } else {
                        buttons.push({
                            text: step.advancedOptions && step.advancedOptions.buttonCloseText 
                                ? step.advancedOptions.buttonCloseText 
                                : (tourConfig.buttonCloseText || 'Finish'),
                            action: tour.complete,
                            classes: 'x-page-tour-button x-page-tour-button__complete'
                        });
                    }
                }
                
                // Get the template element for this step to extract ID
                const allStepTemplates = element.querySelectorAll('template.x-page-tour-step, .brxe-xpagetourstep');
                const stepElement = allStepTemplates[index];
                
                // Use template ID if available, otherwise generate one
                const stepId = (stepElement && stepElement.id) ? stepElement.id : `step-${index}`;
                
                // Get title from the template element if available
                let stepTitle = '';
                if (stepElement && stepElement.getAttribute('data-step-title')) {
                    stepTitle = stepElement.getAttribute('data-step-title');
                } else if (step.title) {
                    stepTitle = step.title;
                }
                
                // Create step options
                const stepOptions = {
                    id: stepId,
                    title: stepTitle,
                    text: step.text || '',
                    buttons: (step.advancedOptions && step.advancedOptions.showButtons === false) || buttons.length === 0 ? [] : buttons
                };
                
                // Store the original step element for later reference
                const originalStepElement = allStepTemplates[index];
                
                // Add selector if provided
                if (step.selector || originalStepElement) {
                    // Check if component scope is enabled
                    const isComponentScoped = tourConfig.component === true && tourConfig.parentComponent;
                    
                    // Find the actual DOM element to use for this step
                    let targetElement = null;
                    
                    // First try to use the data-target-element attribute if it exists
                    if (originalStepElement && originalStepElement.hasAttribute('data-target-element')) {
                        const targetSelector = originalStepElement.getAttribute('data-target-element');
                        if (targetSelector) {
                            if (isComponentScoped) {
                                const tourElement = document.querySelector(`[data-x-id="${elementId}"]`) || element;
                                const componentElement = tourElement.closest(`.brxe-${tourConfig.parentComponent}`);
                                if (componentElement) {
                                    targetElement = componentElement.querySelector(targetSelector);
                                }
                            } else {
                                targetElement = document.querySelector(targetSelector);
                            }
                        }
                    }
                    
                    // If no target element found yet, try using the step.selector
                    if (!targetElement && step.selector) {
                        if (isComponentScoped) {
                            // Find the component that contains this page tour element
                            const tourElement = document.querySelector(`[data-x-id="${elementId}"]`) || element;
                            const componentElement = tourElement.closest(`.brxe-${tourConfig.parentComponent}`);
                            
                            if (componentElement) {
                                // Find the target element within this specific component instance
                                targetElement = componentElement.querySelector(step.selector);
                            }
                        } else {
                            // Find the element globally
                            targetElement = document.querySelector(step.selector);    
                        }
                    }
                    
                    // If we have a target element from either method
                    if (targetElement) {
                        // Get position from step data attribute if available
                        // originalStepElement is already defined above
                        let position = 'auto';
                        
                        if (originalStepElement && originalStepElement.getAttribute('data-step-position')) {
                            position = originalStepElement.getAttribute('data-step-position');
                        } else if (step.position) {
                            position = step.position;
                        }
                        
                        stepOptions.attachTo = {
                            element: targetElement,
                            on: position
                        };
                    } else if (tourConfig.targetNotFound === 'skip') {
                        // Skip this step if target not found and targetNotFound is set to 'skip'
                        return; // Skip adding this step to the tour
                    } 
                    
                    if (step.highlightElement === 'true' || 
                        (tourConfig.defaultHighlight && step.highlightElement !== 'false')) {
                        stepOptions.highlightClass = 'x-page-tour-highlight';
                        
                    }
                }
                
                // Add scroll behavior if enabled
                // Check if scrollTo is explicitly set on the step or if defaultScrollTo is enabled
                if (step.scrollTo === 'true' || 
                    (tourConfig.defaultScrollTo && step.scrollTo !== 'false')) {
                    stepOptions.scrollTo = {
                        behavior: 'smooth',
                        block: 'center',
                    };
                }
                
                // Use custom class if provided, otherwise empty string
                let stepClasses = step.customClass || '';
                
                // Add any classes from the template element
                if (stepElement && stepElement.className) {
                    // Extract all classes including Bricks-specific ones
                    const templateClasses = Array.from(stepElement.classList).join(' ');
                    
                    if (templateClasses) {
                        stepClasses += ' ' + templateClasses;
                    }
                }
                
                // Add step to tour
                const tourStep = tour.addStep({ ...stepOptions, classes: stepClasses });
                
                // If the template element has an ID, set it on the step element when it's rendered
                if (stepElement && stepElement.id) {
                    tourStep.on('show', () => {
                        if (tourStep.el) {
                            tourStep.el.id = stepElement.id;
                        }
                    });
                }
            });
            
            // Handle exit on overlay click
            if (tourConfig.exitOnOverlayClick) {
                // Use event delegation to handle clicks on the overlay
                // This works even after the overlay is moved to our container
                document.addEventListener('click', (e) => {
                    // Check if the clicked element or any of its parents has the overlay class
                    let element = e.target;
                    while (element) {
                        if (element.classList && element.classList.contains('shepherd-modal-overlay-container')) {
                            tour.cancel();
                            break;
                        }
                        element = element.parentElement;
                    }
                });
            }
            
            // Store tour instance
            if (typeof window.xPageTour === 'undefined') {
                window.xPageTour = {};
            }
            if (typeof window.xPageTour.Instances === 'undefined') {
                window.xPageTour.Instances = {};
            }
            
            if (elementId) {
                window.xPageTour.Instances[elementId] = tour;
            }
            
            // Add start button event listener
            const startButton = element.querySelector('.x-page-tour-button');
            if (startButton) {
                startButton.addEventListener('click', () => {
                    tour.start();
                });
            }
            
            // Custom buttons are handled in the 'before-show' and 'show' events for each step
            
            // Add event listeners for creating and removing container
            tour.on('start', () => {
                const tourContainer = createTourContainer();
                // Update stepsContainer after tour is created
                tour.options.stepsContainer = tourContainer;
                
                // Set up a MutationObserver to detect when the modal overlay is added to the DOM
                if (tourConfig.useModalOverlay) {
                    const bodyObserver = new MutationObserver((mutations) => {
                        mutations.forEach((mutation) => {
                            if (mutation.addedNodes.length) {
                                mutation.addedNodes.forEach((node) => {
                                    // Check if the added node is the modal overlay
                                    if (node.classList && node.classList.contains('shepherd-modal-overlay-container')) {
                                        // Move the modal overlay into our container
                                        tourContainer.appendChild(node);
                                        // Stop observing once we've found and moved the overlay
                                        bodyObserver.disconnect();
                                    }
                                });
                            }
                        });
                    });
                    
                    // Start observing the body for changes
                    bodyObserver.observe(document.body, { childList: true });
                }
            });    
            
            // Set up step options with button visibility rules
            tourConfig.steps.forEach((step, index) => {
                const isFirstStep = index === 0;
                const isLastStep = index === tourConfig.steps.length - 1;
                
                // Add beforeShowPromise to each step to handle button visibility
                if (tourConfig.autoHideCustomButtons) {
                    const originalStep = tour.steps[index];
                    if (originalStep) {
                        // Store the original beforeShowPromise if it exists
                        const originalBeforeShowPromise = originalStep.options.beforeShowPromise;
                        
                        // Set a new beforeShowPromise that handles button visibility
                        originalStep.options.beforeShowPromise = function() {
                            return new Promise((resolve) => {
                                const nextSelector = tourConfig.customNextSelector || '.x-tour-next';
                                const backSelector = tourConfig.customBackSelector || '.x-tour-back';
                                const completeSelector = tourConfig.customCompleteSelector || '.x-tour-complete';
                                
                                // Add CSS to hide buttons that shouldn't be visible
                                const styleId = 'x-tour-button-visibility';
                                let styleEl = document.getElementById(styleId);
                                
                                if (!styleEl) {
                                    styleEl = document.createElement('style');
                                    styleEl.id = styleId;
                                    document.head.appendChild(styleEl);
                                }
                                
                                // Create CSS rules for button visibility
                                let cssRules = '';
                                
                                if (isFirstStep) {
                                    cssRules += `${backSelector} { display: none !important; }`;
                                }
                                
                                if (!isLastStep) {
                                    cssRules += `${completeSelector} { display: none !important; }`;
                                }
                                
                                if (isLastStep) {
                                    cssRules += `${nextSelector} { display: none !important; }`;
                                }
                                
                                styleEl.textContent = cssRules;
                                
                                // Call the original beforeShowPromise if it exists
                                if (originalBeforeShowPromise) {
                                    return originalBeforeShowPromise().then(resolve);
                                } else {
                                    resolve();
                                }
                            });
                        };
                    }
                }
            });

            // Add event handlers for step events
            tour.on('show', (e) => {
                // Dispatch custom event for step shown
                if (e && e.step) {
                    // Find the step index by matching the step ID with the tour steps
                    let stepIndex = 0;
                    const stepId = e.step.id;

                    
                    
                    // Try to find the index by matching the ID directly
                    const matchingIndex = tour.steps.findIndex(s => s.id === stepId);
                    if (matchingIndex !== -1) {
                        stepIndex = matchingIndex;
                    } else {
                        // Fall back to the old parsing method
                        stepIndex = parseInt(stepId.replace('step-', '')) || 0;
                    }
                    
                    // Find the original template element for this step
                    const stepTemplates = element.querySelectorAll('template.x-page-tour-step, .brxe-xpagetourstep');
                    const originalStepElement = stepTemplates[stepIndex];
                    
                    // Save tour state if remember progress is enabled
                    if (rememberProgress && elementId) {
                        // Always save the current step index
                        const currentStepIndex = stepIndex;
                        const indexToSave = currentStepIndex;
                        
                        
                        // Save the step index directly
                        tourStorage.saveTourState(elementId, indexToSave);
                    }
                    
                    if (originalStepElement) {
                        // Create and dispatch event on the original step element
                        const stepEvent = new CustomEvent('x_page_tour_step:shown', {
                            detail: {
                                tourId: elementId,
                                stepId: e.step.id,
                                stepIndex: stepIndex
                            },
                            bubbles: true
                        });
                        originalStepElement.dispatchEvent(stepEvent);
                        
                        // Also dispatch on document for global listeners
                        document.dispatchEvent(stepEvent);
                    }
                }
                
                setTimeout(() => {
                    bricksRunAllFunctions();

                    if (e.step && e.step.el && e.step.el instanceof Element) {

                        if (typeof doExtrasMediaPlayer == 'function') {
                            doExtrasMediaPlayer(e.step.el)
                        } else if ( typeof xDoMediaPlayerLoad == 'function' ) {
                            xDoMediaPlayerLoad()
                        }

                        xBricksExtrasElementsInit(e.step.el);

                        /* prevent media player stuck in buffering */
                        e.step.el.querySelectorAll('media-player').forEach(mediaPlayer => {
                            if (mediaPlayer.hasAttribute('data-can-play')) {
                                console.log('remove buffering')
                                //mediaPlayer.removeAttribute('data-buffering');
                            }
                        });
                        
                        // Add event listeners to custom buttons within the step content
                        const nextSelector = tourConfig.customNextSelector || '.x-tour-next';
                        const backSelector = tourConfig.customBackSelector || '.x-tour-back';
                        const completeSelector = tourConfig.customCompleteSelector || '.x-tour-complete';
                        const skipSelector = tourConfig.customSkipSelector || '.x-tour-skip';
                        
                        // Get current step index and total steps for auto-hide functionality
                        let currentStepIndex = 0;
                        const stepId = e.step.id;
                        const matchingIndex = tour.steps.findIndex(s => s.id === stepId);
                        if (matchingIndex !== -1) {
                            currentStepIndex = matchingIndex;
                        } else {
                            // Fall back to the old parsing method
                            currentStepIndex = parseInt(stepId.replace('step-', '')) || 0;
                        }
                        const totalSteps = tourConfig.steps.length;
                        const isFirstStep = currentStepIndex === 0;
                        const isLastStep = currentStepIndex === totalSteps - 1;
                        
                        // Next buttons in step content
                        e.step.el.querySelectorAll(nextSelector).forEach(button => {
                            // Remove existing listeners to prevent duplicates
                            button.replaceWith(button.cloneNode(true));
                            // Add new listener to the new button
                            const newButton = e.step.el.querySelector(nextSelector);
                            if (newButton) {
                                newButton.addEventListener('click', () => {
                                    tour.next();
                                });
                            }
                        });
                        
                        // Back buttons in step content
                        e.step.el.querySelectorAll(backSelector).forEach(button => {
                            // Remove existing listeners to prevent duplicates
                            button.replaceWith(button.cloneNode(true));
                            // Add new listener to the new button
                            const newButton = e.step.el.querySelector(backSelector);
                            if (newButton) {
                                newButton.addEventListener('click', () => {
                                    tour.back();
                                });

                            }
                        });
                        
                        // Complete buttons in step content
                        e.step.el.querySelectorAll(completeSelector).forEach(button => {
                            // Remove existing listeners to prevent duplicates
                            button.replaceWith(button.cloneNode(true));
                            // Add new listener to the new button
                            const newButton = e.step.el.querySelector(completeSelector);
                            if (newButton) {
                                newButton.addEventListener('click', () => {
                                    tour.complete();
                                });
                                
                                // Auto-hide is now handled in the before-show event with CSS
                            }
                        });
                        
                        // Skip buttons in step content - always visible
                        e.step.el.querySelectorAll(skipSelector).forEach(button => {
                            // Remove existing listeners to prevent duplicates
                            button.replaceWith(button.cloneNode(true));
                            // Add new listener to the new button
                            const newButton = e.step.el.querySelector(skipSelector);
                            if (newButton) {
                                newButton.addEventListener('click', () => {
                                    tour.cancel();
                                });
                                // Skip button is always visible, no auto-hide
                            }
                        });

                        e.step.el.querySelectorAll('media-player').forEach(mediaPlayer => {
                            
                        });
                    }
                }, 100);
            });

            // Add event handler for step hidden
            tour.on('hide', (e) => {
                if (e && e.step) {
                    // Use the same method to determine step index as elsewhere
                    let stepIndex = 0;
                    const stepId = e.step.id;
                    const matchingIndex = tour.steps.findIndex(s => s.id === stepId);
                    if (matchingIndex !== -1) {
                        stepIndex = matchingIndex;
                    } else {
                        // Fall back to the old parsing method
                        stepIndex = parseInt(stepId.replace('step-', '')) || 0;
                    }
                    
                    // Find the original template element for this step
                    const stepTemplates = element.querySelectorAll('template.x-page-tour-step, .brxe-xpagetourstep');
                    const originalStepElement = stepTemplates[stepIndex];
                    
                    if (originalStepElement) {
                        // Create and dispatch event on the original step element
                        const stepEvent = new CustomEvent('x_page_tour_step:hidden', {
                            detail: {
                                tourId: elementId,
                                stepId: e.step.id,
                                stepIndex: stepIndex
                            },
                            bubbles: true
                        });
                        originalStepElement.dispatchEvent(stepEvent);
                        
                        // Also dispatch on document for global listeners
                        document.dispatchEvent(stepEvent);
                    }
                }
            });
            
            // Add event handlers for tour completion and cancellation
            tour.on('complete', () => {
                const tourEvent = new CustomEvent('x_page_tour:completed', {
                    detail: { tourId: elementId },
                    bubbles: true
                });
                element.dispatchEvent(tourEvent);
                
                // Clear saved tour state when tour is completed
                if (rememberProgress) {
                    tourStorage.clearTourState();
                }
                
                // Save completed state for 'show again until complete' feature
                tourStorage.saveCompletedState(elementId);
                
                removeTourContainer();
            });
            
            tour.on('cancel', () => {
                const tourEvent = new CustomEvent('x_page_tour:cancelled', {
                    detail: { tourId: elementId },
                    bubbles: true
                });
                element.dispatchEvent(tourEvent);
                
                // Clear saved tour state when tour is cancelled
                if (rememberProgress) {
                    tourStorage.clearTourState();
                }
                
                removeTourContainer();
            });
            
            // Add event handler for tour start
            tour.on('start', () => {
                const tourEvent = new CustomEvent('x_page_tour:started', {
                    detail: { tourId: elementId },
                    bubbles: true
                });
                element.dispatchEvent(tourEvent);
                
                // Save completion state when tour is first shown
                // This is what matters for 'never show again' - that the user has seen it
                tourStorage.saveCompletionState(elementId);
            });

            // Check if we should auto-start the tour
            const shouldAutoStart = 'page_load' === tourConfig.trigger;
        
        // Check for saved state
        const savedState = rememberProgress ? tourStorage.getTourState() : null;
        
        // Check if this tour has a saved state to resume
        const shouldResume = savedState && savedState.tourId === elementId && rememberProgress;
        
        // Check if this tour has a click trigger
        const hasClickTrigger = 'click' === tourConfig.trigger && tourConfig.clickSelector;
        
        // Check if this tour should be shown based on show_again configuration
        const shouldShowBasedOnConfig = tourStorage.shouldShowTour(
            elementId, 
            tourConfig.showAgain || 'page_load', 
            tourConfig.showAgainDays || 0, 
            tourConfig.showAgainHours || 0
        );
            
            // Check if component scope is enabled
            const isComponentScoped = tourConfig.component === true && tourConfig.parentComponent;
            
            // Set up click trigger if needed
            if (hasClickTrigger) {
                // Find the click trigger element, respecting component scope if enabled
                let clickTriggerElement;
                
                if (isComponentScoped) {
                    // Find the specific tour element by its unique ID
                    const tourElement = document.querySelector(`[data-x-id="${elementId}"]`) || element;
                    // Find the component that contains this specific tour element
                    let componentElement = tourElement.closest(`.brxe-${tourConfig.parentComponent}`);
                    
                    if (componentElement) {
                        // Then find the trigger element within this specific component instance
                        clickTriggerElement = componentElement.querySelector(tourConfig.clickSelector);
                    }
                } else {
                    // Find the element globally
                    clickTriggerElement = document.querySelector(tourConfig.clickSelector);
                }
                
                if (clickTriggerElement) {
                    
                    // Add click event listener
                    clickTriggerElement.addEventListener('click', function(e) {
                        e.preventDefault();
                        // Always start from the beginning when clicked
                        tour.start();
                    });
                } 
            }

            // Check if we should auto-start or resume the tour
            // If rememberProgress is enabled and there's a saved state, prioritize that over show_again settings
            // But once completed, respect the show_again settings
            const shouldAutoStartOrResume = shouldResume || (shouldAutoStart && shouldShowBasedOnConfig);
            
            // Auto-start or resume tour if needed
            if (shouldAutoStartOrResume) {
                // Use a small delay to ensure the page is fully loaded
                setTimeout(() => {
                    // If we should resume a saved tour
                    if (shouldResume && savedState.stepIndex !== undefined) {
                        // Get the saved step index
                        let baseStepIndex = savedState.stepIndex;
                        
                        // If saveNextStep is enabled, add 1 to the index (but don't exceed max steps)
                        const saveNextStep = tourConfig.saveNextStep === 'next';
                        let resumeStepIndex = baseStepIndex;
                        
                        if (saveNextStep) {
                            resumeStepIndex = Math.min(baseStepIndex + 1, tourConfig.steps.length - 1);
                        }
                        
                        if (resumeStepIndex >= 0) {
                            // We need to override the first step's show function to immediately go to our target step
                            // This prevents both steps from showing and avoids triggering cancel events
                            
                            // Store the original show method of the first step
                            const firstStep = tour.steps[0];
                            const originalShow = firstStep.show;
                            
                            // Override the show method for just the first time
                            let hasOverridden = false;
                            firstStep.show = function() {
                                // Restore original show method immediately
                                firstStep.show = originalShow;
                                
                                if (!hasOverridden) {
                                    hasOverridden = true;
                                    
                                    // Use a timeout to ensure the tour is fully initialized
                                    setTimeout(() => {
                                        // Show the target step directly
                                        // Find the step by index rather than assuming ID format
                                        if (tour.steps && tour.steps[resumeStepIndex]) {
                                            tour.show(tour.steps[resumeStepIndex].id);
                                        } else {
                                            // Fallback to old method
                                            tour.show(`step-${resumeStepIndex}`);
                                        }
                                    }, 10);
                                    return this;
                                }
                                
                                // Call original method for subsequent calls
                                return originalShow.apply(this, arguments);
                            };
                            
                            // Start the tour - our override will handle showing the correct step
                            tour.start();
                        } else {
                            tour.start();
                        }
                    } else if (shouldAutoStart) {
                        tour.start();
                    }
                }, 500);
            }

            // Mark as initialized
            element.setAttribute('data-x-tour-initialized', 'true');
        });
    };
    
    // Initialize on document load
    initPageTours(document);
    
    // Register AJAX handler for Bricks builder
    const xPageTourAJAX = xExtrasRegisterAJAXHandler('doPageTour');

    // Expose function
    window.doPageTour = initPageTours;

    if (typeof bricksextras !== 'undefined') {

        bricksextras.pagetour = {
          start: (brxParam) => {
            let target = brxParam?.target || false
            if ( target && target.getAttribute('data-x-id') ) {
                let pageTourInstance = xPageTour.Instances[target.getAttribute('data-x-id')]
                if (xPageTour) {
                   pageTourInstance.start()
                }
            }
          },
        }
      
      }
}

document.addEventListener("DOMContentLoaded", function(e) {
    bricksIsFrontend && xPageTour();
});
