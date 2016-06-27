
	snOAuth = function() {
		this.user = null;
		this.popup = null;
		this.link_classname = 'sn-oauth-popup';				
		this.bindClick();	
		
	}
	

	snOAuth.prototype.setResponseCallback = function(response_callback) {
		this.response_callback = response_callback;
	}
	
	snOAuth.prototype.getLinks = function() {
		
		classname = this.link_classname;
		
		if (document.getElementsByClassName != undefined) {
			return document.getElementsByClassName(classname);
		}
				
		var hasClassName = new RegExp("(?:^|\\s)" + classname + "(?:$|\\s)");
		
		var allElements = document.getElementsByTagName("a");
		var results = [];

		var element;
		for (var i = 0; (element = allElements[i]) != null; i++) {
			var elementClass = element.className;			
			if (elementClass && elementClass.indexOf(classname) != -1 && hasClassName.test(elementClass))			
				results.push(element);			
		}

		return results;
	}
	
	
	snOAuth.prototype.closePopup = function() {
		if (!this.popup) return;
		this.popup.close();
		this.popup = null;
	}
	
	
	snOAuth.prototype.openPopup = function(link) {
		
		this.closePopup();
		
		var screen_width = screen.width;
		var screen_height = screen.height;
		var popup_width = 600;
		var popup_height = 400;
		
		var popup_left = Math.ceil((screen_width-popup_width)/2);
		var popup_top = Math.ceil((screen_height-popup_height)/2);
		
		var popup_params = "menubar=no,location=yes,resizable=no,scrollbars=yes,status=no";
		popup_params = popup_params + ',width=' + popup_width; 
		popup_params = popup_params + ',height=' + popup_height;
		popup_params = popup_params + ',left=' + popup_left;
		popup_params = popup_params + ',top=' + popup_top;
		
		this.popup = window.open(link, '_blank', popup_params);
		
		if (!this.popup) {
			App.displayMessage('warning', 'Please disable popup blockers');
			this.popup = null;
			return false;			
		}
		
		return true;
	}
	
	snOAuth.prototype.clickHandler = function(link) {
		if (!this.openPopup(link.href)) return false;
		return false;
	}
	
	snOAuth.prototype.bindClick = function() {
		var links = this.getLinks();
		
		var links_count = links.length;		
		if (links_count == 0) return;
		var me=this;
		var classname_bound = this.link_classname + '_bound';
		var hasClassNameBound = new RegExp("(?:^|\\s)" + classname_bound + "(?:$|\\s)");
		for(i=0; i<links_count; i++) {
			if (hasClassNameBound.test(links[i].className)) continue;
			links[i].className = links[i].className + ' ' + classname_bound; 
			links[i].onclick = function(link_ref){				
				return function() {
					return me.clickHandler(link_ref)
				}
			}(links[i])
		}
	}
	
	
	snOAuth.prototype.responseListener = function(response) {
		if (typeof (this.response_callback) == 'function') this.response_callback(response);
	}
	
	
	
	
	

