
function xElementName(){

    const extrasElementName = function ( container ) {

        

    }

    extrasElementName(document)

    const xElementNameAJAX = xExtrasRegisterAJAXHandler('doExtrasElementName');

    // Expose function
    window.doExtrasElementName = extrasElementName;

}

document.addEventListener("DOMContentLoaded",function(e){
    
    if ( !bricksIsFrontend ) {
        return;
    }

    xElementName()
})
