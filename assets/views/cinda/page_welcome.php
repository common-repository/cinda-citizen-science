<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<br />
<p><img src="<?php echo CINDA_URL .'/assets/images/logo_cinda.png'?>" /></p>
<h1>Citizen Science Volunteers Network</h1>
<p>Plataforma cliente/servidor para la gestión de campañas de ciencia ciudadana desarrollada para el Instituto Interuniversitario del Sistema Tierra en Andalucía.</p>
<br />

<hr>

<br />
<h2>Descripción</h2>

<p>Esta plataforma funciona en la parte de servidor como un plugin para WordPress (si estás leyendo esta pantalla, ya lo has instalado), cuyo código fuente está <a href="https://github.com/si2info/cinda-server" target="_blank">disponible en GitHub</a>. Este servidor expone un API REST en yourdomain.tld/cindaAPI/ con un conjunto de operaciones (descritas más abajo), que permitirán a voluntarios utilizando un Cliente envío y consulta de Contribuciones a las Campañas que configures aquí.</p>

<p>Como cliente utiliza una aplicación Android que se ha desarrollado también con licencia libre, de modo que puedes utilizarla directamente <a href="https://play.google.com/store/apps/details?id=info.si2.iista.volunteernetworks" target="_blank">descargandola de Google Play</a> y añadiendo la dirección de tu servidor, o bien <a href="https://github.com/si2info/cinda-android" target="_blank">descargar el código</a> y personalizarla o mejorarla en lo que consideres. </p>
<p>Por ejemplo, podrías modificar la aplicación cliente para cambiar el logotipo, nombrar a la App con el nombre de tu servidor, y marcarlo como predeterminado, facilitando de este modo la tarea de configuración a tus futuros voluntarios.</p>
<br />

<hr>

<h2>English Version</h2>
<p>This plugin manages volunteer networks. More specifically, the contributions of these volunteers have configured your campaigns here.</p>

<p>It lets you create "campaigns" in which you need to collect data from different people who want to contribute to your cause. For each campaign you can define a specific data model (using data types that we have implemented, such as geo, images, dictionaries, text, numbers, dates, ...) and can receive contributions of structured data.</p>

<p>The plugin exposes a RESTful API in yourdomain.tld/cindaAPI/ that can be consumed by any client. We have built one for Android (especially nice;-)), but if you want you can program another.</p>

<hr>

<h2>API Methods</h2>

<ul style="list-style:initial; padding-left:50px;">
<li>GET /cindaAPI/server/info/<br/>
Returns general server data</li>

<li>GET /cindaAPI/campaigns/list/<br/>
Campaigns list</li>

<li>GET /cindaAPI/campaign/([0-9]+)/<br/>
Details of a campaign.</li>

<li>GET /cindaAPI/campaign/([0-9]+)/model/<br/>
Data model of contributions to a campaign</li>

<li>GET /cindaAPI/campaign/([0-9]+)/listData/<br/>
List of contributions to a campaign</li>

<li>POST /cindaAPI/campaign/([0-9]+)/sendData/<br/>
Sends a contribution</li>

<li>GET /cindaAPI/campaign/([0-9]+)/listVolunteers/<br/>
List Volunteers registered on the server</li>

<li>GET /cindaAPI/topVolunteers/<br/>
Get the top contributors</li>

<li>POST /cindaAPI/campaign/([0-9]+)/suscribe/<br/>
Subscription to a campaign</li>

<li>POST /cindaAPI/campaign/([0-9]+)/unsuscribe/<br/>
Stop following a campaign</li>

<li>POST /cindaAPI/volunteer/register/<br/>
Register/login of a user on the server. Returns a token required to call protected operations.</li>

<li>POST /cindaAPI/volunteer/update-endpoint/<br/>
Updates the user device endpoint for push notifications</li>

<li>GET /cindaAPI/volunteer/([0-9]+)/<br/>
Data of a volunteer</li>

<li>GET /cindaAPI/contribution/([0-9]+)/<br/>
Details of a contribution</li>

<li>GET /cindaAPI/realtime/contributions/<br/>
A special operation, designed to be called from the companion App to send data to a wearable.</li>

<li>GET /cindaAPI/realtime/nearby-activity/<br/>
Wearable related stuff, in progress...</li>

<li>GET /cindaAPI/realtime/watchface/<br/>
Data to paint on the watch face of and Android Wear smartwatch</li>

<li>GET /cindaAPI/dictionary/([0-9]+)/<br/>
Returns values for a special type of field available on the campaigns</li>

<li>GET /cindaAPI/trackings/<br/>
Returns tracks of routes recorded for a user.</li>

<li>GET /cindaAPI/tracking/([0-9]+)/<br/>
Details of a track</li>

<li>POST /cindaAPI/tracking/send/<br/>
Sends a track (GPX)</li>

<li>GET /cindaAPI/opendata/campaigns/<br/>
One way of show all the info about Campaigns to expose an Open Data platform</li>

<li>GET /cindaAPI/opendata/contributions/<br/>
One way of show all the info about Contributions to expose an Open Data Platform</li>
</ul>
</p>

<hr>
<h1>Descripción detallada</h1>
<p>Hemos intentado que todos los puntos de menú y sus funcionalidades sean bastante autodescriptivos, pero te explicamos aquí qué puedes encontrar en cada uno de ellos.</p>
<br />

<h3>Configuración</h3>
<p>Aquí se definen datos generales del servidor: nombre y descripción que mostrará tu servidor al conectarse un voluntario, y algunos parámetros de servicios externos, como la key del API de javascript Google Maps que necesitarás aportar si queires usar la funcionalidad de estos mapas. </p>
<br />

<h3>Creando tu primera campaña</h3>
<p>1. Accede a <a href="/wp-admin/edit.php?post_type=cinda_campaign">"Campañas"</a> en el menú.</p>
<p>2. Pulsa "Nueva Campaña"</p>
<p>3. Indica los datos generales: Nombre, descripción, resumen, logotipo, imagen decorativa, fechas de inicio y fin, ámbito geográfico, y elige un color de acento que se utilizará en la App como decoración. También puedes decidir si los usuarios de esta campaña podrán o no enviar trackings.</p>

<p>Cuando pulses el botón publicar, tu campaña ya estará disponible (si quieres, puedes publicar en borrador), pero aún necesitarás definir el modelo de datos para cada contribución.</p>

<h4>Modelo de datos</h4>
<p>Puedes añadir tantos datos como quieras a la ficha del modelo, nuestra App cliente móvil interpretará su tipo y mostrará un componente amigable con el usuario para cada uno de ellos.</p>
<p>Los tipos de campo que hay disponibles son: </p>
<ul style="list-style:initial; padding-left:50px;">

	<li>Date y Date and Time: útil para indicar la fecha de la contribución. Normalmente siempre debería añadirse un campo de este tipo (o datetime, que además muestra la hora).</li>
	<li>Geoposition: latitud y longitud de la contribución.</li>
	<li>Description Text: es un campo separador, donde podrás escribir algo de texto para orientar al usuario que va a contribuir datos</li>
	<li>Selection: una lista con valores predefinidos, que será típicamente interpretada como un select html (o spinner en Android).</li>
	<li>Dictionary: una lista extensa, que tiene su propio manteniemiento en el menú de este plugin.</li>
	<li>Image: Una fotografía (puedes añadir varios campos de este tipo).</li>
	<li>File: El caso general del anterior, para adjuntar cualqueir archivo (audio, pdf,...).</li>
	<li>Input Text,	Number,	Textarea: Para aportar texto, datos numéricos, o texto más extenso, respectivamente.</li>
</ul>

<h4>Diccionarios</h4>
<p>Puedes añadir tantos diccionarios como quieras, para luego usarlos en campos del modelo de datos.</p>
<p>Los diccionarios almacenan, además de su nombre y una descripción opcional, una lista no jerárquica compuesta por registros con código, nombre y descripción.</p>
<p>El formato del fichero CSV que necesario aportar para crear un diccionario es: code,name,description (0123456,Term name,Term long description)</p>
<br />

<h3>Exportación de datos</h3>
<p>Todos los datos contribuidos son accesibles en formato JSON a través de las operaciones del API (incluidas dos especialmente pensadas para Open Data, que listan todas las campañas y todas las contribuciones para una campañada dada).</p>
<p>Adicionalmente, puedes exportar las campañas y contribuciones a formato CSV desde su punto de menú</p>
<br />

<hr>
<h3>Lista de voluntarios</h3>
<p>Muestra un listado de los usuarios que han iniciado sesión en el servidor CINDA.</p>
<br />

<hr>

<h3>Envío de notificaciones push</h3>
<p>Envía una notificación push a los dispositivos Android que se han conectado al servidor.</p>
<p>La notificación puede enviarse a todos los usuarios, o solo a los que se hayan suscrito a una campaña concreta.</p>
<p>Además, es posible definir una campaña a la que se redirigirá al usuario al abrir la App Android pulsando en esta notificación (si no hay ninguna definida, simplemente se abrirá la App)</p>
<br />

<hr>

<br />
<h2>App Android Wear</h2>
<p>Como complemento a la App cliente que usan los voluntarios existe un watchface que opcionalmente puede usarse en dispositivos Android Wear vinculados a tu móvil. El objetivo de esta pantalla de reloj es permitir a tus voluntarios identificarse con el proyecto (y recordarle que están colaborando con él, ya que lo verán siempre que miren la hora), facilitando una visión general de la situación de las campañas del servidor al que están conectados.</p>
<p>Con una pantalla dividida en tres zonas de información, ofrecemos de una forma muy visual y en tiempo real datos sobre:</p>
<p>
	<ul style="list-style:initial; padding-left:50px;">
		<li>Número de campañas en el servidor al que estás conectado, y la actividad que tienen en relación a las otras (círculo exterior)</li>
		<li>Máximos contribuidores (voluntarios más actiivos en el servidor, que se muestran en el círculo intermedio)</li>
		<li>Tu actividad, observando la evolución por semanas o meses en el gráfico de la zona  inferior.</li>
	</ul>
</p>
<p>
	<img src="<?php echo CINDA_URL .'/assets/images/mockup_watchface_01.png'?>" style="width: 40%; margin: 0 2%;"  />
	<img src="<?php echo CINDA_URL .'/assets/images/mockup_watchface_02.png'?>" style="width: 40%; margin: 0 2%;"  />
</p>
<br />

<hr>

<br />
<p class="brands">
	<a href="http://www.iista.es/" target="_blank" title="Instituto Interuniversitario de Investigación del Sistema Tierra en Andalucía (IISTA)"><img src="<?php echo CINDA_URL .'/assets/images/logo_iista.png'?>"  /></a>
	<a href="http://www.iecolab.es/" target="_blank" title="Laboratorio de Ecología Terrestre (IECOLAB)"><img src="<?php echo CINDA_URL .'/assets/images/logo_iecolab.png'?>"  /></a>
	<a href="https://www.ugr.es/" target="_blank" title="Universidad de Granada (UGR)"><img src="<?php echo CINDA_URL .'/assets/images/logo_ugr.png'?>" /></a>

	<br/>
	<a href="http://www.si2.info/" target="_blank" title="SI2 Soluciones"><img src="<?php echo CINDA_URL .'/assets/images/logo_si2.png'?>"  /></a>
	<a href="http://wiki.obsnev.es/index.php/P%C3%A1gina_principal" target="_blank" title="SI2 Soluciones"><img src="<?php echo CINDA_URL .'/assets/images/logo_observatoriocambioglobal.png'?>"  /></a>
	<a href="http://osl.ugr.es/" target="_blank" title="SI2 Soluciones"><img src="<?php echo CINDA_URL .'/assets/images/logo_osl.png'?>"  /></a>

</p>
