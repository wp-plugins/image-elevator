/**
* Part of jQuery Migrate plugin.
* It allows to add the support of the browser property into jquery 1.9.0+

* Copyright 2013, OnePress, http://onepress-media.com/portfolio
* Help Desk: http://support.onepress-media.com/
*/

// Limit scope pollution from any deprecated API
(function() {
    if ( jQuery.browser ) return;
    
    var matched, browser;

    // Use of jQuery.browser is frowned upon.
    // More details: http://api.jquery.com/jQuery.browser
    // jQuery.uaMatch maintained for back-compat
    jQuery.uaMatch = function( ua ) {
        ua = ua.toLowerCase();

        var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
            /(webkit)[ \/]([\w.]+)/.exec( ua ) ||
            /(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
            /(msie) ([\w.]+)/.exec( ua ) ||
            ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
            [];

        return {
            browser: match[ 1 ] || "",
            version: match[ 2 ] || "0"
        };
    };

    matched = jQuery.uaMatch( navigator.userAgent );
    browser = {};

    if ( matched.browser ) {
        browser[ matched.browser ] = true;
        browser.version = matched.version;
    }

// Chrome is Webkit, but Webkit is also Safari.
    if ( browser.chrome ) {
        browser.webkit = true;
    } else if ( browser.webkit ) {
        browser.safari = true;
    }

    jQuery.browser = browser;

    jQuery.sub = function() {
        function jQuerySub( selector, context ) {
            return new jQuerySub.fn.init( selector, context );
        }
        jQuery.extend( true, jQuerySub, this );
        jQuerySub.superclass = this;
        jQuerySub.fn = jQuerySub.prototype = this();
        jQuerySub.fn.constructor = jQuerySub;
        jQuerySub.sub = this.sub;
        jQuerySub.fn.init = function init( selector, context ) {
            if ( context && context instanceof jQuery && !(context instanceof jQuerySub) ) {
                context = jQuerySub( context );
            }

            return jQuery.fn.init.call( this, selector, context, rootjQuerySub );
        };
        jQuerySub.fn.init.prototype = jQuerySub.fn;
        var rootjQuerySub = jQuerySub(document);
        return jQuerySub;
    };

})();

/**
* Clipboad API to insert images into the post content
* 
* Copyright 2013, OnePress, http://onepress-media.com/portfolio
* Help Desk: http://support.onepress-media.com/
*/

jQuery(document).ready(function($){

    window.clipboardContext = {
        
        supportClipboardAPI: false,
        
        init: function() {
            var self = this;
            this.disabled = false;
            
            this.editorHolder = $("#postdivrich");
            this.contentWrap = $("#wp-content-wrap");
            
            this.initClipboardEvents();
            
            if ( !this.isActive() ) $(".image-insert-controller").addClass("disabled");
            $(".image-insert-controller").click(function(){
                self.setState( $(this).is(".disabled") );
                return false;
            });
                
            this.createControllerTooltip();
        },

        /**
         * Inits the clipboard evetns for editors on the page.
         */
        initClipboardEvents: function(){
            if ( $(".wp-editor-area").length == 0 ) return;

            var self = this;
 
            // - creates a capture to insert images from clipboards
            
            // fake editable content
            this.createPasteCapture();

            // focuses on the fake editable content when ctrl+v is presed
            this.preventPaste = function() {
                if (!document.activeElement) return;

                // we can insert images only into wp editor
                if ( !$(document.activeElement).is(".wp-editor-area") ) return;

                self.saveCurrentSelection();
                $("#paster").focus();
            }

            var ctrlDown = false, metaDown = false;         
            var ctrlKey = 17, metaKey = 224, vKey = 86;

            $(document).keydown(function(e) {
                if (e.keyCode == ctrlKey) ctrlDown = true;
                if (e.keyCode == metaKey) metaDown = true; 
            }).keyup(function(e) {
                if (e.keyCode == ctrlKey) ctrlDown = false;
                if (e.keyCode == metaKey) metaDown = false;      
            });

            $(document).keydown(function(e){
                if ( !self.isClipboardActive() ) return;
                if ( (ctrlDown || metaDown ) && e.keyCode == vKey) {
                    if ( $.browser.msie ) {
                        self.setState("error", "Sorry, IE doesn't support yet inserting images from clipboard.");
                        return;
                    }
                    if ( $.browser.opera ) {
                        self.setState("error", "Sorry, Opera doesn't support yet inserting images from clipboard.");
                        return;
                    }
                    self.preventPaste();
                };
            });
            
            // catchs the "paste" event to upload image on a server
            
            document.onpaste = function (e) {

                if (!document.activeElement) return;
                if ( !self.isClipboardActive() ) return;
                
                // we can insert images only into wp editor or editable content
                if ( 
                    !$(document.activeElement).is(".wp-editor-area") && 
                    !$(document.activeElement).attr('contenteditable') ) return;
                
                    var options = {
                        before: function() {
                            self.setLoadingStateForTextarea();    
                        },
                        success: function(html) {
                            self.insertHtmlForTextarea(html);
                        },
                        error: function() {
                            self.clearLoadingStateForTextarea();    
                        }
                    };

                    if ( e.clipboardData && e.clipboardData.items )  {
                        self.uploadFromClipboard(e, options);
                    } else {
                        self.uploadFromCapture(options);   
                    }
            };
            
            // catch drag & drop evetns
            
            this.initDragAndDropEvents();
        },
        
        // --------------------------------------------------------------------------
        // Working with states
        // --------------------------------------------------------------------------
        
        /**
         * Is the plugin is active now?
         */
        isActive: function() {
            if ( this.editorHolder.length == 0 ) return false;
            if ( this.isDisabled() ) return false;
            
            if ( window.localStorage ) {
                value = localStorage.getItem('insertimage-state');
                if ( value ) return value == "yes" ? true : false;
            }
            
            return true;
        },
        
        isDisabled: function() {
            return this.disabled;
        },
        
        isClipboardActive: function() {
            if ( !this.isActive() ) return false;
            if ( !window.imgevr_clipboard_active ) return false;
            return true;
        },
        
        isDropAndDragActive: function() {
            if ( !this.isActive() ) return false;
            if ( !window.imgevr_dragdrop_active ) return false;
            
            if ( this.contentWrap.hasClass("tmce-active") || this.contentWrap.hasClass("html-active") ) return true;
            return false;
        },
        
        /**
         * Sets state a set the working Clipboard Images
         */
        setState: function( state, message ) {
            var buttons = $(".image-insert-controller");

            if ( state == "error" ) {
                
                buttons.addClass("disabled");   
                if ( window.localStorage ) localStorage.setItem('insertimage-state', "no");

                $(".insertimage-status")
                    .addClass('insertimage-status-disabled')
                    .removeClass('insertimage-status-enabled');
                    
                this.showErrorState(message ? message : "Image Elevator <strong>deactivated</strong>.");
                
            } else if ( state ) {
                
                buttons.removeClass("disabled");
                if ( window.localStorage ) localStorage.setItem('insertimage-state', "yes");

                $(".insertimage-status")
                    .addClass('insertimage-status-enabled')
                    .removeClass('insertimage-status-disabled');    
                    
                this.showActiveState(message ? message : "Image Elevator <strong>activated</strong>.");
                
            } else {
                
                buttons.addClass("disabled");   
                if ( window.localStorage ) localStorage.setItem('insertimage-state', "no");

                $(".insertimage-status")
                    .addClass('insertimage-status-disabled')
                    .removeClass('insertimage-status-enabled');
                    
                this.showDeactiveState(message ? message : "Image Elevator <strong>deactivated</strong>.");
            }
        },
        
        getTooltipOptions: function( extraClasses, leftPosition ) {

            var options = {
                content: "...",
                position: {
                    my: !leftPosition ? 'bottom center' : 'top left',
                    at: !leftPosition ? 'top center' : 'bottom center',
                    target: $(".image-insert-controller")
                },
                show: {
                    event: null,
                    solo: true
                },
                hide: {
                    event: 'unfocus'
                },
                style: {
                    classes: 'qtip2-light qtip2-shadow qtip2-rounded ' + extraClasses
                }
            };
            return options;
        },
        
        showActiveState: function( message ) {
            var tooltip = $("#qtip2-active-state");
            
            if ( tooltip.length == 0 ) {
                tooltip = $("<div id='qtip2-active-state'></div>").appendTo("body");
                var options = this.getTooltipOptions( "clipboad-images-active-state" );
                tooltip.qtip2(options);
            }
            
            var api = tooltip.qtip2("api");
            api.set("content.text", message);
          
            api.show();
        },
        
        showDeactiveState: function( message ) {
            var tooltip = $("#qtip2-deactive-state");
            
            if ( tooltip.length == 0 ) {
                tooltip = $("<div id='qtip2-deactive-state'></div>").appendTo("body");
                var options = this.getTooltipOptions( "clipboad-images-deactive-state" );
                tooltip.qtip2(options);
            }
            
            var api = tooltip.qtip2("api");
            api.set("content.text", message);
            
            api.show();
        },
        
        showErrorState: function( message ) {
            var tooltip = $("#qtip2-error-state");
            
            if ( tooltip.length == 0 ) {
                tooltip = $("<div id='qtip2-error-state'></div>").appendTo("body");
                var options = this.getTooltipOptions("qtip2-red clipboad-images-error-state", true);
                tooltip.qtip2(options);
            }
            
            var api = tooltip.qtip2("api");
            api.set("content.text", message);
            
            api.show();
        },
        
        showError: function(response, data) {
            
            if ( data && data.error ) {
                this.showErrorState(data.error);
            } else {
                if ( response.responseText) {
                    this.showErrorState(response.responseText);
                } else {
                    this.showErrorState("Sorry, unknown error. Please contact the support.");
                }
            }
        },
        
        createControllerTooltip: function() {
            if ( !window['clipboard-images-build'] || window['clipboard-images-build'] == 'free' ) return;
                        
            this.controllerTooltip = $("<div>").appendTo("body").qtip2({
                content: {
                    text: '<p>Click to activate/deactivate.</p><p>Also visit the <a href="options-general.php?page=imgevr_settings">settings page</a></p>'
                },
                position: {
                    my: 'left center',
                    at: 'right center',
                    target: $(".image-insert-controller")
                },
                show: {
                    solo: true,
                    target: $(".image-insert-controller")
                },
                hide: {
                    event: "unfocus",
                    target: $(".image-insert-controller"),
                    inactive: 2000 
                },
                style: {
                    classes: 'qtip2-light qtip2-shadow qtip2-rounded clipboad-images-hint-state'
                }
            });
        },
        
        // --------------------------------------------------------------------------
        // Drag & Drop
        // --------------------------------------------------------------------------
        
        initDragAndDropEvents: function() {
            if ( window['clipboard-images-build'] == 'free' ) return;
            var self = this;
            
            var dragAndDropHolder = this.editorHolder;
            if ( dragAndDropHolder.length == 0) return;
            
            var dragAndDropOverlay = self.createDragAndDropOverlay( dragAndDropHolder );

            $(document).bind("dragleave drop", function(e){
                if ( !self.isDropAndDragActive() ) return false;
                e.preventDefault();
                return false;
            });
            
            $(document).bind("dragover", function(e){
                if ( !self.isDropAndDragActive() ) return false;
                e.preventDefault();
                self.showDragAndDropOverlay( dragAndDropHolder );
                return false;
            });
            
            // current drop & down element
            this._dropDownElement = null;
            // allows or now to hide drop down overlay
            this._allowToHideDropDownOverlay = true;
            // delay that is used to check if mouse pointer leave the drop & down overlay
            this._dropDownOverlayDalay = 1000;
            // checks state of user activities during the delay
            this._hasAnyActivitiesOnDropDownOverlay = false;
            
            self._availableToHideOverlay = true;
            
            // the couple of events that is used to check if the mouse pointer 
            // is really above the drop & drag overlay or not, it's hard yeeah...
            
            dragAndDropOverlay.add(dragAndDropOverlay.find("*"))
                .bind('dragover', function(e){
                    if ( !self.isDropAndDragActive() ) return false;
                    e.preventDefault();
                        
                    self._hasAnyActivitiesOnDropDownOverlay = true;
                    self._allowToHideOverlay = ( self._dropDownElement == this && dragAndDropOverlay[0] == this );
                    self._dropDownElement = this;
                    
                    self.higlightDragAndDropOverlay( dragAndDropHolder );
                    
                    return false;
                })
                .bind('dragleave', function(e){
                    if ( !self.isDropAndDragActive() ) return false;
                    e.preventDefault();
                    
                    self._dragDropAutoHideDelay = 3000;
                    self._hasAnyActivitiesOnDropDownOverlay = false;
                    if ( !self._allowToHideDropDownOverlay ) return false;
  
                    self._dropDownOverlayDalay = 300;

                    var timer = setInterval(function(){
                       self._dropDownOverlayDalay -= 150;

                       if ( 
                           self._dropDownOverlayDalay <= 0 && 
                           self._allowToHideDropDownOverlay && 
                           !self._hasAnyActivitiesOnDropDownOverlay ) 
                       {
                           self.unhiglightDragAndDropOverlay( dragAndDropHolder );
                           clearInterval(timer);
                       }
                    }, 150);
                    
                    return false;
                });

            dragAndDropHolder[0].ondrop = function(e) {
                if ( !self.isDropAndDragActive() ) return false;
                e.preventDefault();
                
                self.hideDragAndDropOverlay( dragAndDropHolder );
                
                // if a browser doesn't support it
                if ( !e.dataTransfer || !e.dataTransfer.files || e.dataTransfer.files.length == 0 ) return false;
                
                if ( e.dataTransfer.files.length > 1 ) {
                    self.showErrorState("Please, paste only one image per time.");
                    return false;
                }

                var file = e.dataTransfer.files[0];
                var type = file.type || file.mediaType;
   
                if ( type ) {
                    
                    if ( 
                        type.indexOf("png") == -1 && 
                        type.indexOf("jpeg") == -1 && 
                        type.indexOf("gif") == -1 && 
                        type.indexOf("jpg") == -1 ) {
                            self.showErrorState("Please, make sure that you paste one of the following files: png, jpg, gif.");
                            return false;
                        }
                }
   
                var options = ( self._currentEditorTab == "html" ) 
                    ? { 
                        before: function() {
                            self.saveCurrentSelection();
                            self.setLoadingStateForTextarea();    
                        },
                        success: function(html) {
                            self.insertHtmlForTextarea(html);
                        },
                        error: function() {
                            self.clearLoadingStateForTextarea();    
                        }
                    } 
                    : {
                        before: function(){
                            var editor = tinyMCE.activeEditor;
                            editor.selection.setContent(self.getPreloaderHtml());
                        },
                        success: function(html){
                            var editor = tinyMCE.activeEditor;
                            self.insertImageHtml(editor, html);
                        },
                        error: function() {
                            var editor = tinyMCE.activeEditor;
                            self.removePlaceholder(editor);
                        }
                    };
                    
                if ( options.before ) options.before();

                self.uploadImage({
                   image: file,
                   type: type,
                   name: file.name || file.fileName
               }, options);
               
                return false;
            }
        },
        
        createDragAndDropOverlay: function( holder ) {
            
            var overlay = $("<div class='onp-drag-and-drop-overlay'></div>");
            var text = $("<span class='onp-drag-and-drop-text'><strong class='onp-drag-and-drop-strong'>Drag & Drop Here</strong><em class='onp-drag-and-drop-em'>Please remember only images allowed.</em></span>");
            
            overlay.append(text);
            holder.prepend( overlay );
            return overlay;
        },
        
        showDragAndDropOverlay: function( holder ) {
            this._dragDropAutoHideDelay = 3000;
            
            if ( this._dragAndDropOverlayCreated ) return;
            var self = this;
            
            this._currentEditorTab = this.contentWrap.is(".tmce-active") ? "visual" : "html";
            
            var overlay = holder.find(".onp-drag-and-drop-overlay");
            var parent = overlay.parent();
            overlay.css({
                width: parent.innerWidth(),
                height: parent.innerHeight()
            });
            overlay.fadeIn();
            
            
            self._dragDropAutoHideDelayTimer = setInterval(function(){
                self._dragDropAutoHideDelay -= 500;
                if ( self._dragDropAutoHideDelay <= 0 ) {
                    self.hideDragAndDropOverlay(holder);
                    if ( self._dragDropAutoHideDelayTimer ) clearInterval( self._dragDropAutoHideDelayTimer );
                    self._dragDropAutoHideDelayTimer = null;
                }
            }, 500);
            
            this._dragAndDropOverlayCreated = true;
        },
        
        hideDragAndDropOverlay: function( holder ) {
            
            var overlay = holder.find(".onp-drag-and-drop-overlay");
            overlay.fadeOut();
            
            if ( this._dragDropAutoHideDelayTimer ) clearInterval( this._dragDropAutoHideDelayTimer );
            this._dragDropAutoHideDelayTimer = null;
                    
            this._dragAndDropOverlayCreated = false;
        },
        
        higlightDragAndDropOverlay: function( holder ) {
            var overlay = holder.find(".onp-drag-and-drop-overlay");
            overlay.addClass('onp-hover');
        },
        
        unhiglightDragAndDropOverlay: function( holder ) {
            var overlay = holder.find(".onp-drag-and-drop-overlay");
            overlay.removeClass('onp-hover');
        },
        
        // --------------------------------------------------------------------------
        // Methods for uploading
        // --------------------------------------------------------------------------
        
        /**
         * Uploads image by using Clipbord API.
         */
        uploadFromClipboard: function(e, options) {
            var self = this;

            // if the image is inserted into the textarea, then return focus
            self.returnFocusForTextArea();
            
            // read data from the clipborad and upload the first file
            var items = e.clipboardData.items;
            for (var i = 0; i < items.length; ++i) {
                if (items[i].kind == 'file' && items[i].type.indexOf('image/') !== -1) {

                    if ( options.before ) options.before();

                    // only paste 1 image at a time
                    e.preventDefault();

                    // uploads image on a server
                    this.uploadImage({
                        image: items[i].getAsFile(),
                        type: items[i].type,
                        ref: 'clipboard'
                    }, options);

                    return;
                }
            }
        },
        
        /**
         * Uploads image by using the capture.
         */
        uploadFromCapture: function(options) {
            var self = this;
            
            if ( options.before ) options.before();

            var timeout = 5000, step = 100;
            $("#paster").html("");

            var timer = setInterval(function(){

                var html = $("#paster").html();

                // in nothing found, carry on to wait
                if ( html.length > 0 ) {
                    clearInterval(timer);

                    if ( html.indexOf("<img") == 0 ) {

                        self.uploadImage({
                            image: $("#paster img").attr('src'),
                            type: null,
                            ref: 'dragdrop'
                        }, options);  

                    } else {
                        self.insertHtmlForTextarea($("#paster").text());
                    }

                } else {

                    timeout = timeout - step;
                    if ( timeout < 0 ) {
                        clearInterval(timer);
                        // call error?
                    }
                    return;
                }

            }, 100);

            setTimeout(function(){
               self.returnFocusForTextArea(); 
            }, 500);
        },
        
        /**
         * Uploads image data on the server.
         */
        uploadImage: function(data, options) {
            var self = this;
            this.lockTabs();

            // adds the default error handler
            var oldCallback = options.error;
            options.error = function(response, data){
                if ( oldCallback ) oldCallback(response, data);
                self.showError(response, data);
            };
            
            var oData = new FormData();
            oData.append('file', data.image);
            oData.append('action', 'imageinsert_upload');

            oData.append('imgMime', data.type); 
            if ( data.name ) oData.append('imgName', data.name); 
            if ( data.ref ) oData.append('imgRef', data.ref);      
            oData.append('imgParent', this.getCurrentPostId());

            var req = new XMLHttpRequest();
            req.open("POST", ajaxurl);

            req.onreadystatechange = function() {
                if (req.readyState == 4) {
                    if(req.status == 200) {

                        try {
                            var response = JSON.parse( req.responseText );
                        }
                        catch(e) {
                            self.unlockTabs();
                            options.error(req, null);
                            return;
                        }

                        if ( response && response.error ) {
                            self.unlockTabs();
                            options.error(req, response);
                            return;
                        }

                        var html = self.getHtmlCodeToInsert(response);
                        self.unlockTabs();
                        
                        options.success(html, response);
                        return;
                    }

                    self.unlockTabs();
                    options.error(req, null);
                }
            }

            req.send(oData);  
        },
        
        lockTabs: function() {
            var self = this;
            
            var inited = self.tabsClearened;
            
            $("#wp-content-editor-tools a.wp-switch-editor").each(function(index, item){
                 $(item).addClass('disabled');
                if ( !inited ) {
                    $(item).prop("onclick", null);
                    self.tabsClearened = true;
                } else {
                    $(item).unbind("click.elevator");
                }
            }); 
        },
        
        unlockTabs: function() {
            var self = this;
            
            $("#wp-content-editor-tools a.wp-switch-editor").each(function(index, item){
                $(item).removeClass("disabled");
                $(item).bind("click.elevator", function(e){
                    switchEditors.switchto(this);
                });
            }); 
        },
        
        /**
         * Creates athe capture.
         */
        createPasteCapture: function() {
            
            this.paster = $("<div id='paster'></div>").attr({
                "contenteditable": "true",
                "_moz_resizing": "false"
            }).css({
                "position": "absolute",
                "height": "1",
                "width": "1",
                "opacity": "0",
                "outline": "0",
                "overflow": "auto",
                "z-index": "-9999"})
            .prependTo("body");  
        },
        
        // --------------------------------------------------------------------------
        // Methods for working with textarea
        // --------------------------------------------------------------------------
        
        /**
         * Returns html code of image to insert.
         */
        getHtmlCodeToInsert: function(data) {
            if ( data.id  ) {
                return "<img alt='' class='alignnone size-full wp-image-" + data.id + "' src='" + data.url + "' />";   
            }
            return "<img alt='' class='alignnone size-full' src='" + data.url + "' />";
        },
        
        /**
         * Sets a loading label into a current textarea of the editor.
         */
        setLoadingStateForTextarea: function() {
            if ( !this.selection ) return;
            var selection = this.selection;
            
            var state = "[{ loading ... }]";
            
            this.original = selection.end > 0
                ? selection.editor.value.slice(selection.start, this.selection.end)
                : "a";

            selection.editor.value = selection.editor.value.slice(0, selection.start) + state 
                + selection.editor.value.slice(selection.end);
            
            this.selection = {
                start: selection.start,
                end: selection.start + state.length,
                editor: selection.editor
            }
        },
        
        /**
         * Remores the loading label from the current editor textarea.
         */
        clearLoadingStateForTextarea: function() {
            if ( !this.selection ) return;
            this.selection.editor.value = 
                this.selection.editor.value.slice(0, this.selection.start) 
                + this.original 
                + this.selection.editor.value.slice(this.selection.end);
            
            this.selection = null;
        },
        
        /**
         * Returns a focus on the current editor textarea.
         */
        returnFocusForTextArea: function() {
            if ( !this.selection ) return;
            
            $(this.selection.editor).focus();
            this.selection.editor.selectionStart =  this.selection.start;
            this.selection.editor.selectionEnd = this.selection.end;     
        },
        
        insertHtmlForTextarea: function(html) {
            if ( !this.selection ) return;
            this.selection.editor.value = 
                this.selection.editor.value.slice(0, this.selection.start) 
                + html 
                + this.selection.editor.value.slice(this.selection.end);
            
            $(this.selection.editor).focus();
            this.selection.editor.selectionStart =  this.selection.end + html.length;
            this.selection.editor.selectionEnd = this.selection.editor.selectionStart;
            
            this.selection = null;
        },
        
        /**
         * Credits:
         * http://stackoverflow.com/questions/3964710/replacing-selected-text-in-the-textarea
         */
        getInputSelection: function(editor) {
            var start = 0, end = 0;

            if (typeof editor.selectionStart == "number" && typeof editor.selectionEnd == "number") {
                start = editor.selectionStart;
                end = editor.selectionEnd;
            }

            return {
                start: start,
                end: end,
                editor: editor
            };
        },
        
        saveCurrentSelection: function() {
            var self = this;
            
            if (!document.activeElement || !$(document.activeElement).is(".wp-editor-area")) {
                var editor = self.contentWrap.find(".wp-editor-area");
                this.selection = {
                    start: editor.val().length,
                    end: editor.val().length,
                    editor: editor[0]
                };
            } else {
                this.selection = this.getInputSelection(document.activeElement);
            }
        },
        
        // --------------------------------------------------------------------------
        // Methods for working with TinyMCE editor
        // --------------------------------------------------------------------------
        
        /**
         * Inserts the image html into the editor replacing the preloader.
         */
        insertImageHtml: function( editor, html ) {
            editor = ( !editor) ? tinyMCE.activeEditor : editor;
            var preloader = $(editor.getDoc()).find("img[data-type=preloader]");
            if ( preloader.length == 0 ) return;

            preloader.after(html);
            preloader.remove();
        },
        
        removePlaceholder: function( editor ) {
            editor = ( !editor) ? tinyMCE.activeEditor : editor;
            var preloader = $(editor.getDoc()).find("img[data-type=preloader]");
            preloader.parent().remove();
        },
        
        /**
         * Creteats html for the preloader.
         */
        getPreloaderHtml: function() {
            var preloader = window.clipboardImagesAssets + "/img/circle-preloader.gif";
            return "<p><img data-type='preloader' src='" + preloader + "' alt='' /></p>";
        },
       
        // --------------------------------------------------------------------------
        // Helpers
        // --------------------------------------------------------------------------
        
        /**
         * Returns current post id pr nothing.
         */
        getCurrentPostId: function() {
            return jQuery("#post_ID").length > 0 ? jQuery("#post_ID").val() : null;  
        }
    };
    
    $(function(){
        clipboardContext.init();
    });
});