/**
 * sellector_tinymce_button.js
 *
 * Copyright, Sellector GmbH
 */

(function() {
    tinymce.PluginManager.add('sellector_button', function( editor, url ) {
        editor.addButton( 'sellector_button', {
            title: 'Add Sellector',
            icon: 'icon sellector-button-icon',
//            text: 'Add Sellector',
            onclick: function() {
                editor.windowManager.open( {
                    title: 'Select Sellector and configure.',
                    body: [
                        {
                            type: 'listbox', 
                            name: 'sellector', 
                            label: 'Choose from your Sellectors', 
                            values: getSellectors()
                        },
                        {
                            type: 'textbox',
                            name: 'reswidth',
                            label: 'Result Box width',
                            value: '79%'
                        },
                        {
                            type: 'textbox',
                            name: 'resheight',
                            label: 'Result Box height',
                            value: '800px'
                        }
                    ],
                    onsubmit: function( e ) {
                        //alert(JSON.stringify(e.data.sellector,0,5));
                        function assureFormat(dimension){
                            var defaultAppendix = "px";
                            if (dimension && dimension == parseInt(dimension)) {
                                dimension += defaultAppendix;
                            }
                            return dimension;
                        }
                        var selwidth = "20%";
                        var reswidth = "80%";
                        var resheight = "800px";
                        var spi;
                        if (e.data.reswidth) {
                            reswidth = assureFormat(e.data.reswidth);
                        }
                        if (e.data.resheight) {
                            resheight = assureFormat(e.data.resheight);
                        }
                        if (e.data.sellector && e.data.sellector.spi) {
                            spi = e.data.sellector.spi;;
                        }
                        if (spi) {
                            // clear old sellector short codes
                            editor.setContent(editor.getContent().replace(/\[sellector_.[^\]]*\]/gi,''));
                            
                            var content = "";
                            // generate sellector Input short codes
                            if (e.data.sellector.selectBoxIds) {
                                var boxes = e.data.sellector.selectBoxIds.toString().split(' ');
                                for (var i in boxes) {
                                    var box = boxes[i].split('|');
                                    content +="[sellector_selectbox divId='" + box[0];
                                    if(box[1])
                                        content += "' width='" + box[1];
                                    content += "']";
                                    ++i;
                                }
                            }
                            // generate sellector Result short code
                            content += "[sellector_resultbox width='" + reswidth + "' height='" + resheight + "' spi='" + spi + "']";
                            editor.insertContent(content);
                        }
                    }
                });
                function getSellectors() {
                    var data = {'action': 'get_sellector_posts'};
                    var q = jQuery.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: data,
                        async: false,
                        dataType: 'json'
                    });      
                    //alert(JSON.stringify(q,0,5));
                    var sellectors = q.responseJSON;
                    return sellectors;
                }
            }
  /*            onclick: function() {
                editor.insertContent('Hello World!');
            }*/

/*type: 'menubutton',
            menu: [
                {
                    text: 'Menu item I',
                    value: 'Text from menu item I',
                    onclick: function() {
                        editor.insertContent(this.value());
                    }
                }
           ]*/
        });
    });
})();