// CINDA FIELDS TABLE FUNCTION
(function( $ ){
   $.fn.cinda = function() {
	   
	   // Variables globales
	   var $table = $(this),
	   $time = 500,
	   $post_ID = $('input[name="post_ID"]').val(),
	   $addNewFieldHTML = "";
	   
	   // LISTENERS
	   
	   // Boton añadir nuevo campo
	   $('.addnew').on('click',function(e){
		   e.preventDefault();
		   $this = $(this);
		   $('i', $this).addClass('fa-spinner fa-pulse');
		   
		   if($addNewFieldHTML == ""){
			   
			   $.ajax({
				   method: 'POST',
				   url: ajaxurl,
				   data: {
						'action': 'cinda_new_field'
					},
					success: function(response){
						$addNewFieldHTML = response;
						
						// Eliminar TR NO FIELDS
						if($table.find('tr.nofields').length > 0){
							$('tr.nofields', $table).fadeOut($time).fadeIn($time/2).fadeOut($time,function(){
							   $(this).remove();
							   // Añadir nueva fila
							   $('> tbody', $table).append($addNewFieldHTML);
							   // Actualizar posiciones
							   updatePositions();
							   // Desplegar formulario
							   editFieldOpen($('.new-field'));
							   // Desactivar efecto spiner
							   $('i', $this).removeClass('fa-spinner fa-pulse');
						   });
						}else{
							// Añadir nueva fila
							$('> tbody', $table).append($addNewFieldHTML);
							// Actualizar posiciones
							updatePositions();
							// Desplegar formulario
							editFieldOpen($('.new-field'));
							// Desactivar efecto spiner
							$('i', $this).removeClass('fa-spinner fa-pulse');
						}
							
					},
					error: function(e){
						alert("Error al procesar la solicitud, inténtenlo de nuevo.");
					}
			   });
			   
		   }else{
			   // Add HTML
			   $('> tbody', $table).append($addNewFieldHTML);

				// Actualizar posiciones
				updatePositions();
				
				// Desplegar formulario
				editFieldOpen($('tr:last-child', $table));
				
				$('i', $this).removeClass('fa-spinner fa-pulse');
		   }
			   
	   });
	   
	   $('.discardall').on('click',function(){
		   
		   $('> tbody > tr', $table).each(function(){
			   resetField( $(this) );
			   updateRow( $(this) );
		   });
		   
		   
	   });
   
	   $('.saveall').on('click',function(){
		   disableExitAlert();
	   });
	   
	   // Click Boton Editar campo
	   $($table).on( 'click', 'button.edit', function(e){
		   
		   var $edit = false;
		   var $tr = $(this).closest('tr.field');
		   var $form_table = $tr.find('table.field');
		   var $id = $form_table.find('input.field_id').val();
		   
		   // Aviso de posible perdida de datos
		   // Si aun no tiene id, no mostrar aviso.
		   // Si tiene ID pero se acepta el aviso, se podrá editar.
		   if(!isNaN($id)){
			   if(confirm('Modificar este campo puede significar pérdida de datos. ¿Desea continuar?')){
				   $edit = true;
			   }else{
				   $edit = false;
			   }
		   }else{
			   $edit = true;
		   }
		   
		   if($edit){
			   // Abrir formulario
			   editFieldOpen($tr);
		   }
	   });
	   
	   // Click boton Eliminar campo
	   $($table).on( 'click', 'button.delete', function(e){
		   e.preventDefault();
		   $this = $(this);
		   $('i', $this).addClass('fa-spinner fa-pulse');
		   
		   var $delete = false, // Deshabilitado por defecto
		   $tr = $(this).closest('tr.field'), // Fila (<tr>)
		   $form_table = $tr.find('table.field'), // Tabla con el formulario
		   $id = $form_table.find('input.field_id').val(); // Id del campo

		   // Aviso de posible perdida de datos
		   // Si aun no tiene id, no mostrar aviso.
		   // Si tiene ID pero se acepta el aviso, se podrá editar.
		   if($id != ""){
			   if(confirm('Eliminar este campo conlleva posible pérdida de datos. ¿Desea continuar?')){
				   $delete = true;
			   }else{
				   $delete = false;
			   }
		   }else{
			   deleteRow($tr);
			   $('i', $this).removeClass('fa-spinner fa-pulse');
			   return;
		   }
		   
		   // Si se autoriza el eliminar
		   if($delete){
			   
			   $.ajax({
				   method: 'POST',
				   url: ajaxurl,
				   data: {
						'action': 'cinda_field_delete',
						'id': $id,
						'id_campaign': $post_ID 
					},
					success: function(response){
						parseInt(response);
						if(Boolean(response)){
							deleteRow($tr);
						}else{
							alert('Error al procesar la solicitud.');
						}
						$('i', $this).removeClass('fa-spinner fa-pulse');	
					},
					error: function(e){
						alert("Error al procesar la solicitud, inténtenlo de nuevo.");
					}
			   });

		   }
	   });
	   
	   // Boton aceptar
	   $($table).on('click','button.save', function(e){
		   var $tr = $(this).closest('tr.field');
		   e.preventDefault();
		   updateRow($tr);
	   });
	   
	   // Boton Descartar cambios
	   $($table).on('click', 'a.discard', function(e){
		   var $tr = $(this).closest('tr.field'); // Fila (<tr>)
		   e.preventDefault();
		   resetField($tr);
		   updateRow($tr);
	   });
	   
	   // Boton descartar todos los cambios
	   $('a.discard').on('click',function(e){
		   e.preventDefault();
		   $('> tbody > tr', $table).each(function(){
			   resetField($(this));
		   })
	   });
	   
	   // ORDENACION (SORTABLE)
	   
	   // Ajustar ancho tabla para ser sortable
	   $('> thead > tr > th, > tbody > tr > td, > tbody > tr > th', this).each(function () {
	        var cell = $(this);
	        cell.width(cell.width());
	    });
		
	   // Añadir opcion sortable a la tabla
	   $("> tbody", $table).sortable({
		    items: '> tr',
		    forcePlaceholderSize: true,
		    placeholder:'must-have-class',
		    start: function (event, ui) {
		        // Build a placeholder cell that spans all the cells in the row
		        var cellCount = 0;
		        $('td, th', ui.helper).each(function () {
		            // For each TD or TH try and get it's colspan attribute, and add that or 1 to the total
		            var colspan = 1;
		            var colspanAttr = $(this).attr('colspan');
		            if (colspanAttr > 1) {
		                colspan = colspanAttr;
		            }
		            cellCount += colspan;
		        });

		        // Add the placeholder UI - note that this is the item's content, so TD rather than TR
		        ui.placeholder.html('<td colspan="' + cellCount + '">&nbsp;</td>');
		    },
		    stop: function(){
		    	// Actualizar posiciones
		    	updatePositions();
		    	// Alert al salir
		    	enableExitAlert();
		    }
		}).disableSelection();
	   
		
	   $($table).on('focusout','input.field_label',function(){
		   var $tr = $(this).closest('tr.field'),
		   $field_label = $(this),
		   $field_name = $tr.find('input.field_name');

		   if($field_label.val() != "" && $field_name.val() == ""){
			   $.ajax({
				   method: 'POST',
				   url: ajaxurl,
				   data: {
						'action': 'cinda_sanitize_fieldname',
						'text': $field_label.val()
					},
					success: function( response ){
						
						
						$field_name.val( checkFielName(response, response) );					
					},
					error: function(e){
						alert("Error al procesar la solicitud, inténtenlo de nuevo.");
					}
			   });
		   }
		   
		   
	   });
	   
	   
	   $($table).on('change','select.field_type',function(){
		   	var $form_table = $(this).closest('table.field');
		   	// Si la opción es 'select' desplegar options 
		   	if( $('option:selected', $(this)).val() == "select"){
		   		$form_table.find('tr.options').fadeIn(500);
		   	}else if( $form_table.find('tr.options').is(':visible') ){
		   		$form_table.find('tr.options').fadeOut(500);
		   		$form_table.find('tr.options').find('textarea').prop('disabled',true);
		   	} 

		   	if( $('option:selected', $(this)).val() == "dictionary"){
		   		$form_table.find('tr.dictionaries').fadeIn(500);
		   		$form_table.find('tr.dictionaries').find('select').prop('disabled',false);
		   	}else if( $form_table.find('tr.dictionaries').is(':visible') ){
		   		$form_table.find('tr.dictionaries').fadeOut(500);
	   			$form_table.find('tr.dictionaries').find('select').prop('disabled',true);
		   	}
	   });

	   // MINI FUNCTIONS //
	   
	   // Comprobar que no se repite el campo name
	   var checkFielName = function($text, $initialText, $i){
		   $i = $i || 0;

		   $('> tbody > tr input.field_name', $table).each(function(){
			   if( $text == $(this).val() ){
				   $text = $initialText + '_' + (++$i);
				   return checkFielName($text, $initialText, $i);
			   }
		   });

		   return $text;
		   		   
	   }
	   
		// Actualizar posiciones
		var updatePositions = function(){
			var i  = 0;
	    	$(' > tbody > tr', $table).each(function () {
	    		i++;
	    		// Actualizar inputs/selects/textareas
	    		$('input.field_id', this).attr('name','field['+i+'][field_id]');
	    		$('input.field_json', this).attr('name','field['+i+'][field_json]');
	            $('input.field_position', this).attr('name','field['+i+'][position]').val(i);
	            $('input.field_label', this).attr('name','field['+i+'][label]');
	            $('input.field_name', this).attr('name','field['+i+'][name]');
	            $('input.field_required', this).attr('name','field['+i+'][required]');
	            $('textarea.field_description', this).attr('name','field['+i+'][description]');
	            $('textarea.field_options', this).attr('name','field['+i+'][options]');
	            $('select.field_type', this).attr('name','field['+i+'][type]');
	            $('select.field_dictionary', this).attr('name','field['+i+'][dictionary]');
	    	});
	    	
	    	
		}
		
		// Actualizar valores en la tabla
		var updateRow = function($tr){
			// FIELDS INPUTS
			var $error = false,
			$edited = false,
			$field_label = $tr.find('input.field_label'),
			$field_name = $tr.find('input.field_name'),
			$field_description = $tr.find('textarea.field_description'),
			$field_required = $tr.find('input.field_required'),
			$field_type = $tr.find('select.field_type > option:selected'), // > option[value="'+ $obj.field_type +'"]
			$field_options = $tr.find('textarea.field_options');
			$field_dictionary =  $tr.find('select.field_dictionary > option:selected');
			
			// REEMPLAZAAR DATOS
			// Descripción
			$('td.field_description > label', $tr).html( $field_description.val() );
			
			// Label
			if( $field_label.val() != "" ){
				$('td.field_label > label', $tr).html( $field_label.val() );
				// Añadir * Requirido
				if( $field_required.is(':checked') )
					$('td.field_label > label', $tr).append('<span class="required" title="Required">*</span>');
				// Quitar borde rojo
				$field_label.css('border-color','').siblings('span.error').html("");
			}else{
				$error = true;
				$field_label.css('border-color','#F00').siblings('span.error').html("Este campo es obligatorio");
			}
			
			// Name
			if( $field_name.val() != "" ){
				$('td.field_name > label', $tr).html( $field_name.val() );
				$field_name.css('border-color','').siblings('span.error').html("");	
			}else{
				$error = true;
				$field_name.css('border-color','#F00').siblings('span.error').html("Este campo es obligatorio");
			}
			
			
			// Tipo valor
			if($field_type.val() != ""){
				
				$field_type.parent('select').css('border-color','').siblings('span.error').html("");
				
				// Si el tipo escogido es "select" y no se han especificado "options"
				if( $field_type.val() == 'select' && $field_options.val() == "" ){
					$error = true;
					$field_options.css('border-color','#F00').siblings('span.error').html("Este campo es obligatorio");
				}else{
					$('td.field_type > label', $tr).html($field_type.text());
					$field_options.css('border-color','').siblings('span.error').html("");
				}
			
			}else{
				$error = true;
				$field_type.parent('select').css('border-color','#F00').siblings('span.error').html("Este campo es obligatorio");
			}
			
			// Diccionartio
			if($field_type.val() != "dictionary" || ($field_type.val() == "dictionary" && $field_dictionary.val() != "") ){
				$field_dictionary.parent('select').css('border-color','').siblings('span.error').html("");
				
				if($field_type.val() == "dictionary" && $field_dictionary.val() != "")
					$('td.field_description > label', $tr).html($field_description.val() + " <span>("+ $field_dictionary.html() +")</span>" );
				
			}else{
				$error = true;
				$field_dictionary.parent('select').css('border-color','#F00').siblings('span.error').html("Este campo es obligatorio");
			}
			
			if(!$error){
				 editFieldClose($tr);
				 
				 $tr.addClass('edited');
			}
			
		}
		
		// Ventana de Alerta al salir sin guardar
		var exitAlert = function (e) {
		    var confirmationMessage = 'The changes you made will be lost if you navigate away from this page.';
		    (e || window.event).returnValue = confirmationMessage; //Gecko + IE
		    return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
		}
		
		// Activar la alerta al salir sin guardar
		var enableExitAlert = function(){
			window.addEventListener("beforeunload", exitAlert );
		}
		
		// Desactivar la alerta al salir sin guardar
		var disableExitAlert = function(){
			window.removeEventListener("beforeunload", exitAlert);
		}
		
		// Efecto de apertura al editar campo
		var editFieldOpen = function($elem){
		   $elem.addClass('edit');
		   $elem.find('td.field_label').attr('colspan','5').css('width','');
		   $elem.find('div.field_form').slideDown();
	   }
	   
		// Efecto de cierre al editar campo
	   var editFieldClose = function($elem){
		   $($elem).find('div.field_form').slideUp(500,function(){
			   $($elem).removeClass('edit').find('td.field_label').removeAttr('colspan');
		   })
	   }
	   
	   // Descartar cambios en el campo
	   var resetField = function($elem){
		   
		   editFieldClose($elem);
		   
		   var $json = $elem.find('input.field_json').val(),
		   $field_label = $elem.find('input.field_label'),
		   $field_name = $elem.find('input.field_name'),
		   $field_description = $elem.find('input.field_description'),
		   $field_required = $elem.find('input.field_required'),
		   $field_type = $elem.find('select.field_type'), // > option[value="'+ $obj.field_type +'"]
		   $field_options = $elem.find('input.field_options');
		   
		   // Si existe el objeto
		   if(Boolean($json)){
			   var $obj = JSON.parse( $json );
			   
			   $field_label.val( $obj.field_label );
			   $field_name.val( $obj.field_name );
			   $field_description.val( $obj.field_description );
			   $field_options.val( $obj.field_options );
			   if( Boolean($obj.field_required) ){
				   $field_required.prop('checked');
			   }else{
				   $field_required.prop('checked', false);
			   }
			   $field_type.find('> option[value="'+ $obj.field_type +'"]').prop('selected', true); // NOT WORK
	   
		   // Si no existe el objeto (Vacio)
		   }else{
			   deleteRow($elem);
		   }
	   }
	   
	   var deleteRow = function($tr){
		   var $time = 500;
		   
		   $tr.css({'background-color':'#FF0000','color':'#FFFFFF'}).fadeOut($time).fadeIn($time/2).fadeOut($time, function(){
				$(this).remove();
			});
		   
		   updatePositions();
	   }
	   
	   
   };
   
})( jQuery );

(function( $ ){
	   $.fn.cinda_export = function() {
		   
		   $(this).on('click', 'button.export_campaigns', function(){
			   var $button = $(this);
			   $('i', $button).addClass('fa-spinner fa-pulse');
			   $.ajax({
				   method: 'POST',
				   accepts: "text/csv; charset=utf-8",
				   url: ajaxurl,
				   data: {
						'action': 'export_campaigns_list'
				   },
				   success: function( $response ){
						console.log( $response );
						//window.open( $response );
					},
					error: function(e){
						alert("Error al procesar la solicitud, inténtenlo de nuevo.");
					},
					complete: function(){
						$('i', $button ).removeClass('fa-spinner fa-pulse');
					}
			   });
		   });
		   
		   $(this).on('click', 'button.export_campaign', function(){
			   var $id_campaign = $(this).siblings('select').val();
			   alert($id_campaign);
		   });
		   
	   };
})( jQuery );


// Replace IMAGE
(function( $ ){
	var $media_library;
	$('button[name="replace"]').on('click', function(){
		var $button = $(this);
		
		if($media_library){
			$media_library.open();
			return;
		}
		
		$media_library = wp.media({
			title: 'Image replace',
			button: {
				text: 'Select Image'
			},
			multiple: false
		});
		
		$media_library.on('select', function(){
			// Change value input
			if( $button.siblings('input[type="hidden"]').length > 0 )
				$button.siblings('input[type="hidden"]').val( $media_library.state().get('selection').first().id );
			
			// Change image
			if( $button.siblings('a').children('img').length > 0 ){
				$button.siblings('a').attr('href', $media_library.state().get('selection').first().attributes.url );
				$button.siblings('a').children('img').attr('src', $media_library.state().get('selection').first().attributes.url );
			}else{
				$html = '<a href="'+ $media_library.state().get('selection').first().attributes.url +'" target="_blank"><img src="'+ $media_library.state().get('selection').first().attributes.url +'" class="image-table" /></a><br />';
				$button.parent().prepend($html)
			}
			
		});
		
		$media_library.open();
		
	});
	
	
	$('button.cover_image, button.logo_image').on('click', function(){
		var $button = $(this);

		$media_library = wp.media({
			title: 'Select Image',
			button: {
				text: 'Select Image'
			},
			multiple: false
		});
		
		$media_library.on('select', function(){
			// Change value input
			if( $button.siblings('input[type="hidden"]').length == 1 )
				$button.siblings('input[type="hidden"]').val( $media_library.state().get('selection').first().id );
			
			// Change image
			if( $button.siblings('a').children('img').length > 0 ){
				$button.siblings('a').attr('href', $media_library.state().get('selection').first().attributes.url );
				$button.siblings('a').children('img').attr('src', $media_library.state().get('selection').first().attributes.url );
			}else{
				$html = '<a href="'+ $media_library.state().get('selection').first().attributes.url +'" target="_blank"><img src="'+ $media_library.state().get('selection').first().attributes.url +'" class="image-table" /></a><br />';
				$button.parent().prepend($html)
			}
			
		});
		
		$media_library.open();
		
	});

})( jQuery );

// Delete CONTRIBUTION
(function( $ ){
	
	//Click boton Eliminar campo
	$('table#contributions').on( 'click', 'button.delete', function(e){
		   e.preventDefault();
		   $this = $(this);
		   $('i', $this).addClass('fa-spinner fa-pulse');
		   
		   var $delete = false, // Deshabilitado por defecto
		   $tr = $(this).closest('tr'), // Fila (<tr>)
		   $id = $this.attr('data-id');

		   // Aviso de posible perdida de datos
		   // Si aun no tiene id, no mostrar aviso.
		   // Si tiene ID pero se acepta el aviso, se podrá editar.
		   if($id != ""){
			   if(confirm('¿Realmente desea eliminar esta contribución?\nNo habrá vuelta atrás.')){
				   $delete = true;
			   }else{
				   $delete = false;
			   }
		   }else{
			   deleteRow($tr);
			   $('i', $this).removeClass('fa-spinner fa-pulse');
			   return;
		   }
		   
		   // Si se autoriza el eliminar
		   if($delete){
			   
			   $.ajax({
				   method: 'POST',
				   url: ajaxurl,
				   data: {
						'action': 'cinda_contribution_delete',
						'ID': $id,
					},
					success: function(response){
						if(Boolean( parseInt(response) )){
							var $time = 500;
							   
							$tr.css({'background-color':'#FF0000','color':'#FFFFFF'}).fadeOut($time).fadeIn($time/2).fadeOut($time, function(){
								$(this).remove();
							});
							
						}else{
							alert('Error al procesar la solicitud.');
						}
						$('i', $this).removeClass('fa-spinner fa-pulse');	
					},
					error: function(e){
						alert("Error al procesar la solicitud, inténtenlo de nuevo.");
					}
			   });
	
		   }
	});
	
	$("select.select2").select2();	
	
	$("input[type=radio].for_action").on('change', function(){

		var for_elem = '*[data-for="'+$(this).attr("name")+'"]';
		var value = parseInt( $(this).val() );
		if( $(for_elem).length > 0 ){
			
			if( value == 0 ){
				$(for_elem).slideUp();
				$(for_elem).find("input,textearea,select").attr("disabled",true);
			}else if( value == 1){
				$(for_elem).slideDown();
				$(for_elem).find("input,textearea,select").attr("disabled",false);
			}else{
				if( $(for_elem).is(':visible') ){
					$(for_elem).slideUp();
					$(for_elem).find("input,textearea,select").attr("disabled",true);
				}else{
					$(for_elem).slideDown();
					$(for_elem).find("input,textearea,select").attr("disabled",false);
				}
			}
			
		} 
	});
	
})( jQuery );