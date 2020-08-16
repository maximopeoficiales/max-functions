<?php
function getDataConfig()
{
     $args = array(
          'post_type' => 'max_functions_config',
          'posts_per_page' => 1,
          'orderby' => 'ID',
          'order' => 'ASC'
     );

     $datos = new WP_Query($args);
     $data = array();
     while ($datos->have_posts()) : $datos->the_post();
          $data["api_key_google_maps"] = get_field("api_key_google_maps");
          $data["consumer_key"] = get_field("consumer_key");
          $data["consumer_secret"] = get_field("consumer_secret");
          $data["latitud_tienda"] = get_field("latitud_tienda");
          $data["longitud_tienda"] = get_field("longitud_tienda");
     endwhile;
     return $data;
}

function max_functions_get_current_user()
{
     //Los filtros predeterminados usan esto para determinar el usuario actual a partir de las cookies de la solicitud, si están disponibles.
     $user_id = apply_filters('determine_current_user', false);
     /* $user_data = get_userdata($user_id); */
     return [
          'user_id' => $user_id,
     ];
}
add_action("rest_api_init", function () {
     register_rest_route("max_functions/v1", "/get_current_user", array(
          "methods" => "GET",
          "callback" => "max_functions_get_current_user",
          'args'            => array(),
     ));
});

?>


<?php
// template ojito del pedido 
add_action('admin_footer', 'order_preview_template');
function order_preview_template()
{
     $data = getDataConfig();
     if ($data["api_key_google_maps"] != "") {
          # code...

?>
          <style>
               @media(max-width: 768px) {
                    .button_margin {
                         margin-top: 5px !important;
                    }
               }
          </style>
          <script>
               /* funcion ismobile */
               function isMobile() {
                    return (
                         navigator.userAgent.match(/Android/i) ||
                         navigator.userAgent.match(/webOS/i) ||
                         navigator.userAgent.match(/iPhone/i) ||
                         navigator.userAgent.match(/iPod/i) ||
                         navigator.userAgent.match(/iPad/i) ||
                         navigator.userAgent.match(/BlackBerry/i)
                    );
               }
               /* funcion para imprimir */
               function imprimirDiv(imp1) {
                    var printContents = document.querySelector(imp1).innerHTML;
                    w = window.open();
                    //se puede agregar algunos pequeños estilos
                    var style = `
				<style>
					#status{display:none;}
					#tipo_servicio{display:none;}
					h1{font-size:10px;}
					button{display:none;}
					img{margin-top:2em;}
					table {width: 100%; border: 1px solid #000; margin-top:10px; text-align:rigth;}
					th, td {width: 25%;}
					#cambiar_estado{display:none;}
					#edit{display:none;}
					#metodo_envio{display:none;}
					#m_status{display:none;}
					.order-status{display:none;}
					.inner{display:none;}
				</style>
				`;
                    w.document.write(style + printContents);
                    /* w.document.close(); // necessary for IE >= 10
                      w.focus(); // necessary for IE >= 10 */
                    w.print();
                    if (!isMobile()) {
                         w.close();
                    }
                    return true;
               }

               /* fin de impresion */
          </script>
          <script type="text/template" id="tmpl-wc-modal-view-order">
               <div class="wc-backbone-modal wc-order-preview">
				<div class="wc-backbone-modal-content">
					<section class="wc-backbone-modal-main" role="main">
						<header class="wc-backbone-modal-header">
							<mark class="order-status status-{{ data.status }}"><span>{{ data.status_name }}</span></mark>
							<?php /* translators: %s: order ID */ ?>
							<h1><?php echo esc_html(sprintf(__('Order #%s', 'woocommerce'), '{{ data.order_number }}')); ?></h1>
							<button class="modal-close modal-close-link dashicons dashicons-no-alt">
								<span class="screen-reader-text"><?php esc_html_e('Close modal panel', 'woocommerce'); ?></span>
							</button>
						</header>
						<article>
							<?php do_action('woocommerce_admin_order_preview_start'); ?>

							<div class="wc-order-preview-addresses">
								<div class="wc-order-preview-address">
									<h2><?php esc_html_e('Billing details', 'woocommerce'); ?></h2>
									{{{ data.formatted_billing_address }}}

									<# if ( data.data.billing.email ) { #>
										<br>
										<strong><?php esc_html_e('Email', 'woocommerce'); ?></strong>
										<a href="mailto:{{ data.data.billing.email }}">{{ data.data.billing.email }}</a>
									<# } #>

									<# if ( data.data.billing.phone ) { #>
										<strong><?php esc_html_e('Phone', 'woocommerce'); ?></strong>
										<a href="tel:{{ data.data.billing.phone }}">{{ data.data.billing.phone }}</a>
									<# } #>

									<# if ( data.payment_via ) { #>
										<br>
										<strong><?php esc_html_e('Payment via', 'woocommerce'); ?></strong>
										{{{ data.payment_via }}}
									<# } #>
								</div>
								<# if ( data.needs_shipping ) { #>
									<div class="wc-order-preview-address">
										<h2><?php esc_html_e('Shipping details', 'woocommerce'); ?></h2>
										<# if ( data.ship_to_billing ) { #>
											{{{ data.formatted_billing_address }}}
										<# } else { #>
											<a href="{{ data.shipping_address_map_url }}" target="_blank">{{{ data.formatted_shipping_address }}}</a>
										<# } #>

										<# if ( data.shipping_via ) { #>
											<strong><?php esc_html_e('Shipping method', 'woocommerce'); ?></strong>
											{{ data.shipping_via }}
										<# } #>
									</div>
								<# } #>

								<# if ( data.data.customer_note ) { #>
									<div class="wc-order-preview-note">
										<strong><?php esc_html_e('Note', 'woocommerce'); ?></strong>
										{{ data.data.customer_note }}
									</div>
								<# } #>
							</div>

							{{{ data.item_html }}}
							<!-- se agrego validacion para cuando no hayan datos de mapa -->			
                                   <?php do_action('woocommerce_admin_order_preview_end'); ?>
                                   <?php if ($data["api_key_google_maps"] != "") { ?>
							<# if ( data.data.meta_data.length > 1 ) { #>
							<img src="https://maps.googleapis.com/maps/api/staticmap?center={{data.data.meta_data[2].value}},{{data.data.meta_data[3].value}}&zoom=16&size=600x500&markers=color:red%7C{{data.data.meta_data[2].value}},{{data.data.meta_data[3].value}}&key=<?php echo $data["api_key_google_maps"]; ?>" width="100%" height="400px" alt="imagen_mapa">
                                   <# } #>
                                   <?php } ?>
						</article>
						<footer>
							<div class="inner">
								{{{ data.actions_html }}}
								<a class="button button-primary button-large" aria-label="<?php esc_attr_e('Edit this order', 'woocommerce'); ?>" href="<?php echo esc_url(admin_url('post.php?action=edit')); ?>&post={{ data.data.id }}"><?php esc_html_e('Edit', 'woocommerce'); ?></a>
								<button type="button" class="button button-primary button-large button_margin" onclick=imprimirDiv(".wc-backbone-modal-content")>Imprimir Resumen</button>	
							</div>
						</footer>
					</section>
				</div>
			</div>
			<div class="wc-backbone-modal-backdrop modal-close"></div>
			
		</script>
          <!-- fin de modficacion -->
<?php
     }
}
// add the action 
add_action('woocommerce_admin_order_data_after_shipping_address', 'action_woocommerce_admin_order_data_after_shipping_address', 10, 3);
?>
<?php
// define the woocommerce_admin_order_data_after_shipping_address callback 
function action_woocommerce_admin_order_data_after_shipping_address()
{
     $data = getDataConfig();
     if ($data["api_key_google_maps"] != "") {
          # code...
?>
          <?php if (!$_GET["post_type"]) {
          ?>
               <!-- CAMPO url Gmaps -->
               <p id="url_mapa"></p>
              
               <!-- campo url gmaps -->
               <!-- bloque de codigo -->

               <!-- estilos para el modal -->
               <style>
                    * {
                         margin: 0;
                         padding: 0;
                         box-sizing: border-box;
                    }

                    .flex {
                         width: 100%;
                         height: 100%;
                         display: flex;
                         justify-content: space-between;
                         align-items: center;
                    }

                    .textos {
                         padding: 300px;
                         color: #fff;
                         text-align: center;
                    }

                    .modal {
                         display: none;
                         position: fixed;
                         z-index: 1;
                         overflow: auto;
                         left: 0;
                         top: 0;
                         width: 100%;
                         height: 100%;
                         background: rgba(0, 0, 0, 0.452);
                    }

                    .contenido-modal {
                         position: relative;
                         background-color: #fefefe;
                         margin: auto;
                         width: 60%;
                         box-shadow: 0 0 6px 0 rgba(0, 0, 0, .4);
                         animation-name: modal;
                         animation-duration: 1s;
                    }

                    @keyframes modal {
                         from {
                              top: -330px;
                              opacity: 0;
                         }

                         to {
                              top: 0;
                              opacity: 1;
                         }
                    }

                    .modal-header,
                    .footer {
                         padding: 8px 16px;
                         background: #34495e;
                         color: #f2f2f2;
                    }

                    .modal-body {
                         padding: 11px 16px;

                    }

                    span {
                         font-size: 12px;
                    }

                    @media screen and (max-width:768px) {
                         .contenido-modal {
                              width: 90%;
                         }


                    }
               </style>
               <!-- fin de estilos para el modal -->
               <!-- stylis para el spinner -->
               <style>
                    .lds-ring {
                         display: inline-block;
                         position: relative;
                         width: 80px;
                         height: 80px;
                    }

                    .lds-ring div {
                         box-sizing: border-box;
                         display: block;
                         position: absolute;
                         width: 64px;
                         height: 64px;
                         margin: 8px;
                         border: 8px solid #fff;
                         border-radius: 50%;
                         animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
                         border-color: #fff transparent transparent transparent;
                    }

                    .lds-ring div:nth-child(1) {
                         animation-delay: -0.45s;
                    }

                    .lds-ring div:nth-child(2) {
                         animation-delay: -0.3s;
                    }

                    .lds-ring div:nth-child(3) {
                         animation-delay: -0.15s;
                    }

                    @keyframes lds-ring {
                         0% {
                              transform: rotate(0deg);
                         }

                         100% {
                              transform: rotate(360deg);
                         }
                    }

                    .loader {
                         width: 100%;
                         height: 100vh;
                         background: rgba(0, 0, 0, 0.3);
                         display: none;
                         justify-content: center;
                         align-items: center;
                         position: fixed;
                         top: 0;
                         left: 0;
                         z-index: 100000;
                    }
               </style>
               <!-- fin de stylos del spinner -->
               <!-- spinner -->
               <div class="lds-ring loader">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
               </div>
               <!-- spinner -->
               <a href="#" id="abrir" class="button button-primary right">Imprimir Resumen</a>
               <!-- modal de impresion -->

               <div id="miModal" class="modal">
                    <div class="flex" id="flex">
                         <div class="contenido-modal">
                              <div id="wc-backbone-modal-dialog" id="miModal">
                                   <div class="wc-backbone-modal wc-order-preview">
                                        <div class="wc-backbone-modal-content" tabindex="0">
                                             <section class="wc-backbone-modal-main" role="main" id="datos_modal">
                                                  <header class="wc-backbone-modal-header">
                                                       <mark class="order-status status-processing"><span id="m_status">Procesando</span></mark>
                                                       <h1 id="m_n_pedido">Pedido #1224</h1>
                                                       <button class="modal-close modal-close-link dashicons dashicons-no-alt" id="close">
                                                            <!-- <span class="screen-reader-text">Cerrar ventana modal</span> -->
                                                       </button>
                                                  </header>
                                                  <article style="max-height: 481.5px;">

                                                       <div class="wc-order-preview-addresses">
                                                            <div class="wc-order-preview-address">
                                                                 <h2>Detalles del Cliente</h2>
                                                                 <strong>Nombre</strong>
                                                                 <span id="m_cliente"></span>
                                                                 <strong>Correo electrónico</strong>
                                                                 <a href="mailto:maximopeoficiales4@gmail.com" id="m_correo">maximopeoficiales4@gmail.com</a>
                                                                 <strong>Teléfono</strong>
                                                                 <a href="tel:928644700" id="m_telefono">928644700</a><br>
                                                                 <strong>Pago mediante</strong>
                                                                 <span id="m_tipo_mediante">Contra reembolso</span>

                                                            </div>
                                                            <div class="wc-order-preview-address">
                                                                 <h2>Detalles de envío</h2>
                                                                 <a href="https://maps.google.com/maps?&amp;q=Av.%20Nicol%C3%A1s%20de%20Pi%C3%A9rola%201491%2C%20Cercado%20de%20Lima%2015001%2C%20Per%C3%BA%2C%2033%2C%20LIMA%2C%20LIM%2C%20%2C%20PE&amp;z=16" target="_blank" id="m_detalles_envio"></a>

                                                                 <div id="metodo_envio" style="margin-top: 5px;">
                                                                      <strong>Método de envío</strong>
                                                                      Se aplicarán costos de envío según tu dirección en la siguiente página.
                                                                 </div>
                                                                 <br>
                                                                 <strong>Nota Cliente:</strong>
                                                                 <span id="m_nota_cliente"></span>
                                                            </div>
                                                       </div>

                                                       <div id="m_tabla_datos"></div>
                                                       <img src="" width="100%" height="400px" id="imagen_mapa" />
                                                  </article>
                                                  <footer>
                                                       <div class="inner">

                                                            <button class="button button-primary button-large" type="button" role="button" aria-label="Imprimir" onclick="imprimirDiv('#datos_modal');">Imprimir</button>
                                                       </div>
                                                  </footer>
                                             </section>
                                        </div>
                                   </div>
                              </div>
                         </div>
                    </div>
               </div>
               <!-- fin de modal -->
               <!-- peticion ajax ,imagen de google -->
               <script>
                    /* funcion ismobile */
                    function isMobile() {
                         return (
                              navigator.userAgent.match(/Android/i) ||
                              navigator.userAgent.match(/webOS/i) ||
                              navigator.userAgent.match(/iPhone/i) ||
                              navigator.userAgent.match(/iPod/i) ||
                              navigator.userAgent.match(/iPad/i) ||
                              navigator.userAgent.match(/BlackBerry/i)
                         );
                    }
                    /* funcion para imprimir */
                    function imprimirDiv(imp1) {
                         var printContents = document.querySelector(imp1).innerHTML;
                         w = window.open();
                         //se puede agregar algunos pequeños estilos
                         var style = `
										<style>
											#status{display:none;}
											#tipo_servicio{display:none;}
											h1{font-size:10px;}
											button{display:none;}
											img{margin-top:2em;}
											table {width: 100%; border: 1px solid #000; margin-top:10px; text-align:rigth;}
											th, td {width: 25%;}
											#cambiar_estado{display:none;}
											#edit{display:none;}
											#metodo_envio{display:none;}
											#m_status{display:none;}
											.order-status{display:none;}
											.inner{display:none;}
										</style>
										`;
                         w.document.write(style + printContents);
                         /* w.document.close(); // necessary for IE >= 10
                         w.focus(); // necessary for IE >= 10 */
                         w.print();
                         if (!isMobile()) {
                              w.close();
                         }
                         return true;
                    }
                    var resultados_lat, resultados_long;
                    window.onload = () => {
                         document.querySelector(".loader").style.display = "flex";
                         cargarDatos();
                    }


                    //funcion para imprimir un div o seccion
                    function imprimirDiv(imp1) {
                         var printContents = document.getElementById(imp1).innerHTML;
                         w = window.open();
                         //se puede agregar algunos pequeños estilos 
                         var style = `
									<style>
										#status{display:none;}
										#tipo_servicio{display:none;}
										h1{font-size:10px;}
										button{display:none;}
										img{margin-top:2em;}
										table {width: 100%; border: 1px solid #000; margin-top:10px; text-align:rigth;}
										th, td {width: 25%;}
										#cambiar_estado{display:none;}
										#edit{display:none;}
										#metodo_envio{display:none;}
										#m_status{display:none;}
									</style>
									`;
                         w.document.write(style + printContents);
                         /* w.document.close(); // necessary for IE >= 10
                         w.focus(); // necessary for IE >= 10 */
                         w.print();
                         if (!isMobile()) {
                              w.close();
                         }
                         return true;
                    }
                    /* siempre cambiar el url dependiendo el dominio */
                    function cargarDatos() {
                         function obtenerValorParametro(sParametroNombre) {
                              var sPaginaURL = window.location.search.substring(1);
                              var sURLVariables = sPaginaURL.split('&');
                              for (var i = 0; i < sURLVariables.length; i++) {
                                   var sParametro = sURLVariables[i].split('=');
                                   if (sParametro[0] == sParametroNombre) {
                                        return sParametro[1];
                                   }
                              }
                              return null;
                         }
                         let id_servicio = obtenerValorParametro("post");
                         
                         let mapa = document.getElementById("imagen_mapa");
                         (async () => {
                              let url = `<?php echo get_site_url(); ?>/wp-json/wc/v3/orders/${id_servicio}?consumer_key=<?php echo $data["consumer_key"]; ?>&consumer_secret=<?php echo $data["consumer_secret"]; ?>`;
                              let data = await fetch(url)
                              if (data.ok) {
                                   data = await data.json();

                                   function insert(id, dato) {
                                        document.getElementById(id).innerText = dato;
                                   }
                                   insert("m_status", document.querySelector("#select2-order_status-container").innerText)
                                   insert("m_n_pedido", `Pedido # ${id_servicio}`);
                                   insert("m_correo", data.billing.email);
                                   insert("m_cliente", `${data.billing.first_name} ${data.billing.last_name} `);
                                   document.getElementById("m_correo").href = `mailto:${data.billing.email}`;
                                   insert("m_telefono", data.billing.phone);
                                   document.getElementById("m_telefono").href = `mailto:${data.billing.phone}`;
                                   insert("m_tipo_mediante", data.payment_method_title.toUpperCase());
                                   insert("m_nota_cliente", data.customer_note);
                                   /* creata la tabla */
                                   let html = `
							<div class="wc-order-preview-table-wrapper">
								<table cellspacing=" 0 " class="wc-order-preview-table">
								<thead>
									<tr>
									<th class="wc-order -preview-table__column - product">Producto</th>
									<th class="wc-order-preview-table__column - amount">Cantidad</th>
									<th class="wc-order-preview-table__column-- total">Total</th>
									</tr>
								</thead>
								<tbody>
							
							`;
                                   data.line_items.forEach(e => {
                                        html += `
											<tr class="wc-order-preview-table__item wc-order-preview-table__item - 3">
											<td class="wc- order-preview-table__column - product">
												${e.name}
												<div class="wc-order-item-sku"></div>
											</td>
											<td class="wc-order-preview- table__column - cantidad">${e.quantity}</td>
											<td class="wc-order-preview-table__column - total">
												<span class="woocommerce-Price-amount amount">
												<span class="woocommerce-Price-currencySymbol"> ${data.currency_symbol} </span>  ${e.price.toFixed(2)} 
												</span>
											</td>
											</tr>
								`;
                                   });
                                   html += `</tbody>
									</table>
									</div>`;
                                   /* document.getElementById("m_detalles_envio").insertAdjacentHTML("beforeend", html); */
                                   document.getElementById("m_tabla_datos").innerHTML = html;

                                   resultados_lat = data.meta_data[2].value;
                                   resultados_long = data.meta_data[3].value;
                                   /* agrego el link de gmaps */
                                   let div_url_map = document.getElementById("url_mapa");
                                   let titulo_url_map = document.createElement("strong");
                                   let br_element = document.createElement("br");
                                   titulo_url_map.innerText = "Url Gmaps:";
                                   let enlace = document.createElement("a");
                                   enlace.setAttribute("target", "_blank");
                                   /* resultados_lat = datos.latitud;
                                   resultados_long = datos.longitud; */
                                   enlace.href = 'https://maps.google.com/?q=' + resultados_lat + ',' + resultados_long;
                                   enlace.innerHTML = 'https://maps.google.com/?q=' + resultados_lat + ',' + resultados_long;
                                   /* fin de propiedades */
                                   /* agrego al DOM */
                                   div_url_map.appendChild(titulo_url_map);
                                   div_url_map.appendChild(br_element);
                                   div_url_map.appendChild(enlace);
                                   /* fin de agregao al DOM */
                                   mapa.src = `https://maps.googleapis.com/maps/api/staticmap?center=${resultados_lat},${resultados_long}&zoom=16&size=600x400&markers=color:red%7C${resultados_lat},${resultados_long}&key=<?php echo $data["api_key_google_maps"]; ?>`;

                                   /* fin de agrego de link de gmaps */
                                   document.querySelector(".loader").style.display = "none";
                              }
                         })()

                    }
                    let modal = document.getElementById('miModal');
                    let flex = document.getElementById('flex');
                    let abrir = document.getElementById('abrir');
                    let cerrar = document.getElementById('close');

                    function isMobile() {
                         return (
                              (navigator.userAgent.match(/Android/i)) ||
                              (navigator.userAgent.match(/webOS/i)) ||
                              (navigator.userAgent.match(/iPhone/i)) ||
                              (navigator.userAgent.match(/iPod/i)) ||
                              (navigator.userAgent.match(/iPad/i)) ||
                              (navigator.userAgent.match(/BlackBerry/i))
                         );
                    }

                    abrir.addEventListener('click', function() {
                         modal.style.display = 'block';
                         document.querySelector("#wc-backbone-modal-dialog").style.display = "none";

                         document.querySelector("#wc-backbone-modal-dialog").style.display = "";




                         document.querySelector(".woocommerce-layout__header").style.display = 'none';
                         /* if (isMobile()) { */
                         document.querySelector("#wpadminbar").style.display = 'none';
                         /* } */
                    });

                    cerrar.addEventListener('click', function(e) {
                         e.preventDefault();
                         modal.style.display = 'none';
                         document.querySelector(".woocommerce-layout__header").style.display = '';
                         /* if (isMobile()) { */
                         document.querySelector("#wpadminbar").style.display = '';
                         /* 	} */
                    });

                    window.addEventListener('click', function(e) {
                         if (e.target == flex) {
                              modal.style.display = 'none';
                              document.querySelector(".woocommerce-layout__header").style.display = '';

                         }
                    });
               </script>
               <!-- peticion ajax ,imagen de google -->
               <!-- fin de bloque de codigo -->

          <?php } ?>


<?php }
};
function my_custom_js()
{

     if (shortcode_exists("woocommerce_checkout")) { /* detecto si es el checkout */
          $data = getDataConfig();
          if ($data["latitud_tienda"] != "") {
               wp_enqueue_script(
                    'custom_script',
                    esc_url(plugins_url('/js/myscripts.js', dirname(__FILE__)))
               );
               $coordenadas = array(
                    'latitud_tienda' => $data["latitud_tienda"],
                    'longitud_tienda' => $data["longitud_tienda"]
               );
               $datos = array(
                    'l10n_print_after' => 'data_coodenadas = ' . json_encode($coordenadas) . ';'
               );
               wp_localize_script('custom_script', 'data_coodenadas', $datos);
          }
     }
}
function my_custom_head_js()
{
     $data = getDataConfig();
     if (shortcode_exists("woocommerce_checkout")) {
          if ($data["api_key_google_maps"] != "") {
               wp_enqueue_script(
                    'google_maps_script',
                    "https://maps.googleapis.com/maps/api/js?key=" . $data["api_key_google_maps"]
               );
               wp_enqueue_script(
                    'font_awesome_js',
                    "https://kit.fontawesome.com/b5b7f00aae.js"
               );
          }
     }
}

add_action('wp_footer', 'my_custom_js');
add_action('wp_head', 'my_custom_head_js');

?>