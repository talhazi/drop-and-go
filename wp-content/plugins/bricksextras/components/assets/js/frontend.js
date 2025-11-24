HTMLElement.prototype.xslideToggle = function(duration, callback) {
  if (this.clientHeight === 0) {
    _s(this, duration, callback, true);
  } else {
    _s(this, duration, callback);
  }
};

HTMLElement.prototype.xslideUp = function(duration, callback) {
  _s(this, duration, callback);
};

HTMLElement.prototype.xslideDown = function (duration, callback) {
  _s(this, duration, callback, true);
};

function _s(el, duration, callback, isDown) {

  if (typeof duration === 'undefined') duration = 400;
  if (typeof isDown === 'undefined') isDown = false;

  el.style.overflow = "hidden";
  if (isDown) el.style.display = "flex";

  var elStyles        = window.getComputedStyle(el);

  var elHeight        = parseFloat(elStyles.getPropertyValue('height'));
  var elPaddingTop    = parseFloat(elStyles.getPropertyValue('padding-top'));
  var elPaddingBottom = parseFloat(elStyles.getPropertyValue('padding-bottom'));
  var elMarginTop     = parseFloat(elStyles.getPropertyValue('margin-top'));
  var elMarginBottom  = parseFloat(elStyles.getPropertyValue('margin-bottom'));

  var stepHeight        = elHeight        / duration;
  var stepPaddingTop    = elPaddingTop    / duration;
  var stepPaddingBottom = elPaddingBottom / duration;
  var stepMarginTop     = elMarginTop     / duration;
  var stepMarginBottom  = elMarginBottom  / duration;

  var start;

  function step(timestamp) {

    if (start === undefined) start = timestamp;

    var elapsed = timestamp - start;

    if (isDown) {
      el.style.height        = (stepHeight        * elapsed) + "px";
      el.style.paddingTop    = (stepPaddingTop    * elapsed) + "px";
      el.style.paddingBottom = (stepPaddingBottom * elapsed) + "px";
      el.style.marginTop     = (stepMarginTop     * elapsed) + "px";
      el.style.marginBottom  = (stepMarginBottom  * elapsed) + "px";
    } else {
      el.style.height        = elHeight        - (stepHeight        * elapsed) + "px";
      el.style.paddingTop    = elPaddingTop    - (stepPaddingTop    * elapsed) + "px";
      el.style.paddingBottom = elPaddingBottom - (stepPaddingBottom * elapsed) + "px";
      el.style.marginTop     = elMarginTop     - (stepMarginTop     * elapsed) + "px";
      el.style.marginBottom  = elMarginBottom  - (stepMarginBottom  * elapsed) + "px";
    }

    if (elapsed >= duration) {
      el.style.height        = "";
      el.style.paddingTop    = "";
      el.style.paddingBottom = "";
      el.style.marginTop     = "";
      el.style.marginBottom  = "";
      el.style.overflow      = "";
      if (!isDown) el.style.display = "none";
      if (typeof callback === 'function') callback();
    } else {
      window.requestAnimationFrame(step);
    }
  }

  window.requestAnimationFrame(step);
}

/**
* Generic AJAX handler for Bricks elements with automatic event registration
* 
* This function handles the common AJAX event logic used across multiple elements
* and calls the appropriate element-specific function when elements are loaded via AJAX.
* It also automatically registers the event listeners for common Bricks AJAX events.
* 
* @param {string} elementFunction - The name of the element-specific function to call (must be available in window scope)
* @param {boolean} [useSecondParam=false] - Whether to pass true as the second parameter to the element function
* @return {Function} - Event handler function that has been registered with event listeners
*/
function xExtrasRegisterAJAXHandler(elementFunction, useSecondParam = false) {
// Create the handler function
const handler = function(e) {
  // Check if the function exists in the window scope
  if (typeof window[elementFunction] !== 'function') {
    console.warn('BricksExtras: Function ' + elementFunction + ' not found. Make sure it\'s defined before AJAX events are triggered.');
    return;
  }
  
  // Skip if no query ID and no popup element
  if (typeof e.detail.queryId === 'undefined') {
    if (typeof e.detail.popupElement === 'undefined') {
      return;
    } else {
      // Handle popup element case
      window[elementFunction](e.detail.popupElement);
    }
  }

  setTimeout(() => {
    // Try to find element by class first
    if (document.querySelector('.brxe-' + e.detail.queryId)) {
      if (useSecondParam) {
        window[elementFunction](document.querySelector('.brxe-' + e.detail.queryId).parentElement, true);
      } else {
        window[elementFunction](document.querySelector('.brxe-' + e.detail.queryId).parentElement);
      }
    } else {
      // Fall back to searching for the comment
      const treeWalker = document.createTreeWalker(
        document.body,
        NodeFilter.SHOW_COMMENT,
        null,
        false
      );
  
      while (treeWalker.nextNode()) {
        const comment = treeWalker.currentNode;
        if (comment.nodeValue.includes('brx-loop-start-' + e.detail.queryId)) {
          if (useSecondParam) {
            window[elementFunction](comment.parentNode, true);
          } else {
            window[elementFunction](comment.parentNode);
          }
        }
      }
    }
  }, 0);
};

// Register the handler with all the common Bricks AJAX events
document.addEventListener("bricks/ajax/load_page/completed", handler);
document.addEventListener("bricks/ajax/pagination/completed", handler);
document.addEventListener("bricks/ajax/popup/loaded", handler);
document.addEventListener("bricks/ajax/end", handler);

// Return the handler in case it's needed elsewhere
return handler;
}

/**
 * Utility function to process Bricks color values
 * Handles hex values, rgba values, and CSS variables
 * @param {Object|String} colorValue - The color value from Bricks settings
 * @returns {String|Boolean} - A valid CSS color value or false if not found
 */
function xProcessBricksColor(colorValue) {
  // If it's a string, return it directly (likely a direct color value)
  if (typeof colorValue === 'string') {
    return colorValue;
  }
  
  // If it's not an object or is null, return false
  if (!colorValue || typeof colorValue !== 'object') {
    return false;
  }
  
  // Handle CSS variables
  if (colorValue.raw && colorValue.raw.startsWith('var')) {
    const variableName = colorValue.raw.replace(/^var\(([^)]+)\)$/, '$1');
    const value = getComputedStyle(document.documentElement).getPropertyValue(variableName).trim();
    return value || false; // Return the variable value or false
  }
  
  // Handle hex values
  if (colorValue.hex) {
    return colorValue.hex;
  }
  
  // Handle rgb/rgba values
  if (colorValue.rgb) {
    return colorValue.rgb;
  }
  
  // Handle hsl values
  if (colorValue.hsl) {
    return colorValue.hsl;
  }
  
  // No valid color found
  return false;
}

function xBricksExtrasElementsInit(container) {

   /* Pro Accordion */
   if ( typeof doExtrasAccordion == 'function') {
      doExtrasAccordion(container)
  }

  /* Pro Slider */
  if ( typeof doExtrasSlider == 'function') {
      doExtrasSlider(container)
  }

  /* Read More / Less */
  if ( typeof doExtrasReadmore == 'function') {
      setTimeout(() => {
          doExtrasReadmore(container)
          }, 100);
  }

  /* Dynamic Lightbox */
  if ( typeof doExtrasLightbox == 'function') {
      doExtrasLightbox(container, true)
  }

  /* Social share */
  if ( typeof doExtrasSocialShare == 'function') {
      doExtrasSocialShare(container)
  }

  /* OffCanvas */
  if ( typeof doExtrasOffCanvas == 'function') {
      doExtrasOffCanvas(container)
  }

  /* modal */
  if ( typeof doExtrasModal == 'function') {
      doExtrasModal(container)
  }

  /* popover */
  if ( typeof doExtrasPopover == 'function') {
      doExtrasPopover(container)
  }

   /* tabs */
  if (typeof doExtrasTabs == 'function') {
      doExtrasTabs(container)
  }

  /* lottie */
  if (typeof doExtrasLottie == 'function') {
      doExtrasLottie(container, true)
  }

  /* media player */
  if (typeof doExtrasMediaPlayer == 'function') {
      doExtrasMediaPlayer(container)
  }

  /* copy to clipboard */
  if (typeof doExtrasCopyToClipBoard == 'function') {
      doExtrasCopyToClipBoard(container)
  }
  if (typeof doExtrasCopyToClipBoardPopover == 'function') {
      doExtrasCopyToClipBoardPopover(container)
  }

   /* parallax */
  if (typeof doExtrasParallax == 'function') {
      doExtrasParallax(container)
  }

  /* tilt */
  if (typeof doExtrasTilt == 'function') {
      doExtrasTilt(container)
  }

   /* interactions */
  if (typeof doExtrasInteractions == 'function') {
      doExtrasInteractions(container)
  }

  /* table */
  if (typeof doExtrasTable == 'function') {
      doExtrasTable(container)
  }

  /* chart */
  if (typeof doExtrasChart == 'function') {
      doExtrasChart(container)
  }

   /* before after */
  if (typeof doExtrasBeforeAfterImage == 'function') {
      doExtrasBeforeAfterImage(container)
  }

  /* countdown */
  if (typeof doExtrasCountdown == 'function') {
      doExtrasCountdown(container)
  }

   /* image hotspots */
  if (typeof doExtrasImageHotspots == 'function') {
      doExtrasImageHotspots(container)
  }

  /* toggle switch */
  if (typeof doExtrasToggleSwitch == 'function') {
      doExtrasToggleSwitch(container)
  }

  /* favorites */
  if (typeof doExtrasFavorite == 'function') {
      doExtrasFavorite(container)
      if (typeof doExtrasFavoritePopover == 'function') {
          doExtrasFavoritePopover(container)
      }
  }


}