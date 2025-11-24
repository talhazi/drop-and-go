function xCopyToClipBoard() {

    const extrasCopyToClipBoard = function ( container ) {

        let copyText, changeText = false;

        container.querySelectorAll('.brxe-xcopytoclipboard').forEach(copytoclipboard => {

            const configAttr = copytoclipboard.getAttribute('data-x-copy-to-clipboard')
            const config = configAttr ? JSON.parse(configAttr) : {}

            let containerEl = 'true' === config.componentScope && config.parentComponent ? copytoclipboard.closest('.brxe-' + config.parentComponent) : document;
            let buttonDelay = null != config.buttonDelay ? config.buttonDelay : 3000;

            let loopingID = null;

            if (null != config.isLooping) {

                 loopingID = config.isLooping 

                if (!document.querySelector('.brxe-' + config.isLooping)) {
                       if ( null != config.isLoopingComponent ) {
                        loopingID = config.isLoopingComponent
                    }
                }

                containerEl = 'true' === config.componentScope && config.parentComponent ? copytoclipboard.closest('.brxe-' + config.parentComponent) : copytoclipboard.closest('.brxe-' + loopingID); 

            }

            if ( copytoclipboard.querySelector('[data-x-copied]') ) {
                changeText = true;
                copytoclipboard.querySelector('[data-x-copied]').setAttribute('data-x-copy', copytoclipboard.querySelector('[data-x-copied]').innerHTML )
            }

            if ( copytoclipboard.hasAttribute('data-x-copy-text') ) {
                copyText = copytoclipboard.getAttribute('data-x-copy-text')
            } else {
                const copyEl = config.copySelector ? containerEl.querySelector(config.copySelector) : false;
                if ( !copyEl && !document.querySelector('.brx-body.iframe') ) { 
                    console.log('BricksExtras: Element to copy not found, check the selector is correct.')
                    copytoclipboard.dispatchEvent(new Event('x_copy:failed'))
                    return
                }
                if ( !document.querySelector('.brx-body.iframe') ) {

                    if ( copyEl.tagName == 'TEXTAREA') {
                        copyText = copyEl.value
                    } else if ( copyEl.tagName == 'INPUT') {
                        copyText = copyEl.value
                    } else {
                        copyText = copyEl.textContent
                    }
                }
            }

            if (copyText && config.hideEmpty) {
                copytoclipboard.style.display = 'block';
            }

            copytoclipboard.querySelector('button').addEventListener('click', (e) => {

                e.preventDefault()

                if ( document.querySelector('.brx-body.iframe') ) {
                    copytoclipboard.querySelector('button').setAttribute('aria-pressed', 'true');
                    copytoclipboard.querySelector('button').classList.add('x-copy-to-clipboard_copied')
                }

                if ( copytoclipboard.hasAttribute('data-x-copy-text') ) {
                    copyText = copytoclipboard.getAttribute('data-x-copy-text')
                } else {
                    
                    const copyEl = config.copySelector ? containerEl.querySelector(config.copySelector) : false;
                    if ( !copyEl && !document.querySelector('.brx-body.iframe') ) { 
                        console.log('BricksExtras: Element to copy not found, check the selector is correct.')
                        copytoclipboard.dispatchEvent(new Event('x_copy:failed'))
                        return
                    }
                    if ( !document.querySelector('.brx-body.iframe') ) {

                        if ( copyEl.tagName == 'TEXTAREA') {
                            copyText = copyEl.value
                        } else if ( copyEl.tagName == 'INPUT') {
                            copyText = copyEl.value
                        } else {
                            copyText = copyEl.textContent
                        }
                    }
                }
                
                const textArea = document.createElement("textarea");
                textArea.value = copyText;
                textArea.select();
                textArea.setSelectionRange(0, 99999); 

                navigator.clipboard.writeText(copyText).then(() => {
                    if (copyText) {
                        copytoclipboard.querySelector('button').setAttribute('aria-pressed', 'true');
                        copytoclipboard.querySelector('button').classList.add('x-copy-to-clipboard_copied')
                        copytoclipboard.dispatchEvent(new Event('x_copy:copied'))
                        if ( changeText && copytoclipboard.querySelector('[data-x-copied]') ) {
                            copytoclipboard.querySelector('[data-x-copied]').innerHTML = copytoclipboard.querySelector('[data-x-copied]').getAttribute('data-x-copied');
                        }
                        if (config.selectText) {
                            window.getSelection().selectAllChildren(containerEl.querySelector(config.copySelector));
                        }
                    } else {
                        copytoclipboard.dispatchEvent(new Event('x_copy:empty'))
                        console.log('BricksExtras: Copy text empty')
                    }
                  }, () => {
                    copytoclipboard.dispatchEvent(new Event('x_copy:failed'))
                    console.log('BricksExtras: Copy failed')
                   });

                setTimeout(() => {
                    copytoclipboard.querySelector('button').setAttribute('aria-pressed', 'false');
                    copytoclipboard.querySelector('button').classList.remove('x-copy-to-clipboard_copied')
                    copytoclipboard.dispatchEvent(new Event('x_copy:reset'))
                    if ( changeText && copytoclipboard.querySelector('[data-x-copied]') ) {
                        copytoclipboard.querySelector('[data-x-copied]').innerHTML = copytoclipboard.querySelector('[data-x-copy]').getAttribute('data-x-copy');
                    }
                }, buttonDelay);

                   
            })


        })

    }


   extrasCopyToClipBoard(document);

  const xCopyToClipBoardAJAX = xExtrasRegisterAJAXHandler('doExtrasCopyToClipBoard');

  // Expose function
  window.doExtrasCopyToClipBoard = extrasCopyToClipBoard;


}


document.addEventListener("DOMContentLoaded",function(e){
    bricksIsFrontend&&xCopyToClipBoard()
});