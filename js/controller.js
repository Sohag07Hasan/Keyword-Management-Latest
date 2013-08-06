jQuery(function($) {
        
		var cache = {};
        
        $( "#unique_keyword" ).autocomplete({
            minLength: 1,
            source: function( request, response ) {
                var term = request.term;
                              
                if ( term in cache ) {
                    response( cache[ term ] );
                    return;
                }
 
                $.getJSON( AjaxAutoComplete.ajax_url, request, function( data, status, xhr ) {
                    var text = [];
                    var numbers = [];
                    var reA = /[^a-zA-Z]/g;
                    var reN = /[^0-9]/g;
                    for (var key in data) {
                        //console.log(data[key]);
                        var character = data[key].charAt(0);
                        if(character.replace(reA, "") == "")
                        {
                            numbers[key] = data[key];
                        } else if(character.replace(reN, "") == "")
                        {
                            text[key] = data[key];   
                        }
                    }
                    text = text.sort();
                    var all = text.concat(numbers);
                    var finalObject = '[';
                    var finalArray = [];
                    for(var i=0;i<(all.length-1);i++){
                        if(typeof all[i] !== 'undefined')
                        {
                            finalObject += '"'+all[i]+'", ';
                        }
                        //console.log(all[i]);
                    }
                    finalObject += '"'+all[all.length-1]+'" ';
                    finalObject += ']';
                    finalObject = JSON.parse(finalObject);
                    
                    cache[ term ] = finalObject;
                    response( finalObject );
                });
            },
            select: function(event, ui) { 
                var exploded = ui.item.label.split(' ~ ');
                var title = exploded[0];                
                var curr_title = $("#title").val();
                var prev_title = $('#previous_keyword_keyword').val();
                
                //now setting the new keywords
                $('#previous_keyword_keyword').val(title);
                
                if(curr_title.length > 2){
                	
                	var prev_exploded = prev_title.split(' ');
                	
                	for(k=0; k<prev_exploded.length; k++){
                		//alert(prev_exploded[k])
                		curr_title = curr_title.replace(prev_exploded[k], '');
                	}
                	
                	//removing extra white spaces
                	curr_title = curr_title.replace(/\s\s+/g, '');
                	
                	title += ' ' + curr_title;
                }
                                
                $("#title").val(title);
                
            }
        });
    });