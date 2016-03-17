/**
 * js/onlinebizsoft/magemenu.js
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
/**
 * js/onlinebizsoft/magemenu.js
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0 - 2011-07-28 12:24:00
 *
 */
function toggleMenuCreatorMenu(el, over)
{
    if (Element.childElements(el)) {
    var uL = Element.childElements(el)[1];
    var iS = true;
    }
    if (over) {
        Element.addClassName(el, 'over');
        
        if(iS){ uL.addClassName('shown-sub')};
    }
    else {
        Element.removeClassName(el, 'over');
        if(iS){ uL.removeClassName('shown-sub')};
    }
}