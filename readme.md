# Plugin MaxFunctions

Este plugin agregar el mapa de googlemaps en la parte administrativa como tambien en en el frontend al realizar una compra.

- ✅ Mapa interactivo para que el usuario eliga su direccion de recojo

- ✅ Calculo de envio automatico por codigo postal
- ✅ Link de Google Maps para obtener la ubicacion precisa del cliente
- ✅ Impresion de detalle con el mapa

## Requisitos

Tener instalado los siguientes plugins
* Advanced Custom Fields
* Woocommerce(Activar REST API)
* Checkout Fields de Woocommerce

### Instrucciones

1. Instalar el plugin **max functions.zip**
2. Entrar al menu de importacion de Advanced Custom Fields, aqui seleccionamos el archivo **campos_maxfunctions.json** e importar.
3. Crear un configuracion

![image_agregar_configuracion]

![image_agregar_configuracion_menu]
 
 4. Agregar los siguientes campos al plugin **Checkout Fields de Woocommerce**:

- ✅ Activamos el billing_postcode
- ✅ Creamos ce_distanciakm
- ✅ Creamos ce_latitud
- ✅ Creamos ce_longitud

![image_campos_custom_fields]

**Nota:** Deben ser los mismo nombres y crearlos con ese orden si es posible para que funcione perfectamente el codigo

[image_agregar_configuracion]: https://github.com/maximopeoficiales/PluginMaxProyects/blob/master/imgs/agregar_configuracion.PNG
[image_agregar_configuracion_menu]: https://github.com/maximopeoficiales/PluginMaxProyects/blob/master/imgs/agregar_configuracion_menu.PNG
[image_campos_custom_fields]: https://raw.githubusercontent.com/maximopeoficiales/Donasusy.com/master/imgs/CustomFields.PNG