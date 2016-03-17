function showMenuPopup(objMenu, popupId)
{
	objMenu.addClassName('active');
	if(!popupId) return;
    objMenu = $(objMenu.id); var popup = $(popupId); if (!popup) return;
    popup.style.display = 'block';
    var popupWidth = EXPLODEDMENU_POPUP_WIDTH;
    if (!popupWidth) popupWidth = popup.getWidth();
    var pos = wpPopupPos(objMenu, popupWidth);
    popup.style.top = pos.top + 'px';
    popup.style.left = pos.left + 'px';
    if (EXPLODEDMENU_POPUP_WIDTH) popup.style.width = EXPLODEDMENU_POPUP_WIDTH + 'px';
}

function wpPopupPos(objMenu, w)
{
    var pos = objMenu.cumulativeOffset();
    var wraper = $('explodedmenu');
    var posWraper = wraper.cumulativeOffset();
    var wWraper = wraper.getWidth() - EXPLODEDMENU_POPUP_RIGHT_OFFSET_MIN;
    var xTop = pos.top - posWraper.top + EXPLODEDMENU_POPUP_TOP_OFFSET;
    var xLeft = pos.left - posWraper.left;
    if ((xLeft + w) > wWraper) xLeft = wWraper - w;
	if(xLeft < 0) xLeft = 0;
    return {'top': xTop, 'left': xLeft};
}

function hideMenuPopup(element, event, popupId, menuId)
{
	
	element.removeClassName('active');
    element = $(element.id); var popup = $(popupId); if (!popup) return;
    var current_mouse_target = null;
    if (event.toElement)
    {
        current_mouse_target = event.toElement;
    }
    else if (event.relatedTarget)
    {
        current_mouse_target = event.relatedTarget;
    }
    if (!isChildOf(element, current_mouse_target) && element != current_mouse_target)
    {
        if (!isChildOf(popup, current_mouse_target) && popup != current_mouse_target)
        {
            popup.style.display = 'none';
            $(menuId).removeClassName('active');
        }
    }
}

function isChildOf(parent, child)
{
    if (child != null)
    {
        while (child.parentNode)
        {
            if ((child = child.parentNode) == parent)
            {
                return true;
            }
        }
    }
    return false;
}
