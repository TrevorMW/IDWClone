/**
 * Catalog navigation
 *
 * @package    Sprinnet_Accmenu
 * @copyright  Copyright (c) 2010 SPRINNET Systems, s.r.o. (http://www.sprinnet.com/)
 */
DOM = (document.getElementById);
IE = (navigator.userAgent.indexOf("MSIE") != -1);
createTreeMenu = function(options, id){
	if(DOM){
		container = document.getElementById(id);
		if(container){
			treeWalk(container, options);
		}
	}
}
treeWalk = function(container, options){
	isCollapsed = !getStateFromClass(options.expanded, container.className);
	treeItems = document.getElementsByTagName(options.itemElement);
	for(var i=0; i<treeItems.length; i++){
		if(getStateFromClass(options.containerFlag, treeItems.item(i).className)){
			mayHaveContainer = getStateFromClass(options.containerFlag, treeItems.item(i).className);
			if(mayHaveContainer){
				container = getNearestContainer(treeItems.item(i), options.container);
				if(container){
					isItemExpanded = getStateFromClass(options.expanded, treeItems.item(i).className);
					if((isCollapsed && !isItemExpanded) || (!isCollapsed && !isItemExpanded)){
						container.style.display = "none";
					}
					treeItems.item(i).container = container;
					treeItems.item(i).expanded = options.expanded;
					treeItems.item(i).collapsed = options.collapsed;
					treeItems.item(i).onclick = function(e){
						//this.container.style.display = (this.container.style.display == "none")? "block" : "none";
						if(this.container.style.display == "none")
							new Effect.SlideDown(this.container, {duration: 0.2});
						else
							new Effect.SlideUp(this.container, {duration: 0.2});
						setItemClass(this);	
					}
					enableLinkClick(treeItems.item(i), options.itemLink);
				}
			}
		}
	}
}

getStateFromClass = function (str, className){
	return (className.indexOf(str) != -1)? true : false;
}

getNearestContainer = function (elem, className){
	if(elem.nextSibling.className){
		if(getStateFromClass(className, elem.nextSibling.className)){
			return elem.nextSibling;
		}else{
			return getNearestContainer(elem.nextSibling, className);
		}
	}else{
		return getNearestContainer(elem.nextSibling, className);
	}
}
enableLinkClick = function(elem, nodeName){
	links = elem.getElementsByTagName(nodeName);
	for(var i=0; i<links.length; i++){
		links.item(i).onclick = function(e){
			if(e){
				e.stopPropagation();
			}else{
				event.cancelBubble = true;
			}
		}
	}
}
setItemClass = function(node){
	if(node.container.style.display == "none"){
		removeClassName(node, node.collapsed)
		node.className += (" " + node.expanded);
	}else{
		removeClassName(node, node.expanded)
		node.className += (" " + node.collapsed);
	}
}
removeClassName = function(node, str){
	cName = node.className;
	re = new RegExp(str,"gi");
	node.className = cName.replace(re,"");
}
openParent = function(element) {
	//element.up().show();
	//new Effect.Accordion(element.up());
	new Effect.SlideDown(element.up(), {duration: 0.2});
	element.up().previous().addClassName(options.expanded);
	
	return element.up();
}
document.observe('dom:loaded', function() {
	if ($$('#tree-magemenu .activeTreeItem')) {
			$$('#tree-magemenu .activeTreeItem').each(function(element) {
				while (element.up()) {
					element = openParent(element);
				}			
			})
	} else {
		alert($('tree-magemenu').getElementsByClassName('.activeTreeItem'));
	}
});
var currentId = null;

Effect.Accordion = function (contentId) {
	var slideDown = 0.5;
	var slideUp = 0.5;

	contentId = $(contentId);

	if (currentId != contentId) {
		if (currentId == null) {
			new Effect.SlideDown(contentId, {duration: slideDown});
			} else {
			new Effect.SlideUp(currentId, {duration: slideUp});
			new Effect.SlideDown(contentId, {duration: slideDown});
		}
		currentId = contentId; 
	} else {
		new Effect.SlideUp(currentId, {duration: slideUp});
		currentId = null;
	}
};