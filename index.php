
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>three.js webgl - psy - gangnam style</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
		<style>
			body {
				background:#000;
				color: #fff;
				padding:0;
				margin:0;
				font-family:Monospace;
				font-size:13px;
				text-align:center;
				overflow:hidden;
			}

			#info {
				position: absolute;
				top: 30px; width: 100%;
				padding: 5px;
			}

			a {
				color: #fff;
				text-decoration: none;
			}
		</style>
	</head>

	<body>
		<div id="info"><a href="http://www.youtube.com/watch?v=9bZkp7q19f0">psy - gangnam style</a></div>

		<audio id="soundtrack" loop style="display:none">
			<source src="sounds/gangnam.mp3" type='audio/mp3'>
			<source src="sounds/gangnam.ogg" type='audio/ogg'>
		</audio>

		<script src="js/three.max.psy.js"></script>
		<script src="js/libs/stats.min.js"></script>

		<script>


			var MARGIN = 100;

			var SCREEN_WIDTH = window.innerWidth;
			var SCREEN_HEIGHT = window.innerHeight - 2 * MARGIN;

			var container, stats;

			var soundtrack;
			var playing = false;

			var camera, scene, renderer;

			var frames = [];

			var group;
			var time = 0;
			var clock = new THREE.Clock();

			var sceneHUD, cameraOrtho, hudMaterial, materialParticles, timeUniform;

			init();
			animate();

			function init() {

				soundtrack = document.getElementById( "soundtrack" );

				container = document.createElement( 'div' );
				document.body.appendChild( container );

				camera = new THREE.PerspectiveCamera( 60, SCREEN_WIDTH / SCREEN_HEIGHT, 1, 7100 );
				camera.position.y = 480;
				camera.position.z = 2000;

				controls = new THREE.TrackballControls( camera );

				scene = new THREE.Scene();

				scene.fog = new THREE.Fog( 0xf0f0ff, 500, 7100 );
				scene.fog.color.setHSV( 0.6, 0.2, 0.25 );

				// create sprites

				mapA = THREE.ImageUtils.loadTexture( "textures/gangnam/psy_0.png" );
				mapB = THREE.ImageUtils.loadTexture( "textures/gangnam/psy_1.png" );
				mapC = THREE.ImageUtils.loadTexture( "textures/gangnam/psy_2.png" );
				mapD = THREE.ImageUtils.loadTexture( "textures/gangnam/psy_3.png" );

				frames = [ mapD, mapC, mapB, mapA ];

				group = new THREE.Object3D();

				var gridx = 30, gridz = 30;
				var sepx  = 200, sepz = 200;

				for( var x = 0; x < gridx; x ++ ) {

					for( var z = 0; z < gridz; z ++ ) {

						var sprite = new THREE.Sprite( { map: mapD, useScreenCoordinates: false, color: 0xffffff, fog: true } );

						sprite.position.x = - ( gridx - 1 ) * sepx * 0.5 + x * sepx + Math.random() * 0.25 * sepx + ( z % 2 ) * ( gridx * 0.5 + sepx ) * 0.5;
						sprite.position.z = - ( gridz - 1 ) * sepz * 0.5 + z * sepz + Math.random() * 0.25 * sepz - 2500;

						//sprite.color.setHSV(  0.06 + 0.06 * Math.random(), 0.1 + 0.125 * Math.random(), 1 );
						//sprite.color.setHSV(  Math.random(), z/gridz, 1 );

						sprite.offset = Math.random() * 0.025;
						//sprite.visible = false;

						sprite.alphaTest = 0.5;

						group.add( sprite );

					}

				}

				group.position.y = 320;

				scene.add( group );

				//

				var plane = new THREE.Mesh( new THREE.PlaneGeometry( 20000, 20000 ), new THREE.MeshBasicMaterial( { color: 0x555555 } ) );
				plane.rotation.x = -Math.PI/2;
				scene.add( plane );

				// renderer

				renderer = new THREE.WebGLRenderer();
				renderer.setClearColor( scene.fog.color, 1 );
				renderer.setSize( SCREEN_WIDTH, SCREEN_HEIGHT );
				renderer.domElement.style.position = "relative";
				renderer.domElement.style.top = MARGIN + 'px';

				renderer.autoClear = false;

				container.appendChild( renderer.domElement );

				// stats

				stats = new Stats();
				stats.domElement.style.position = 'absolute';
				stats.domElement.style.top = '0px';
				stats.domElement.style.zIndex = 100;
				//container.appendChild( stats.domElement );

				//


				createOverlay();

				//

				window.addEventListener( 'resize', onWindowResize, false );

			}

			function onWindowResize() {

				SCREEN_WIDTH = window.innerWidth;
				SCREEN_HEIGHT = window.innerHeight - 2 * MARGIN;

				camera.aspect = SCREEN_WIDTH / SCREEN_HEIGHT;
				camera.updateProjectionMatrix();

				renderer.setSize( SCREEN_WIDTH, SCREEN_HEIGHT );

			}

			function createOverlay() {

				cameraOrtho = new THREE.OrthographicCamera( -1, 1, 1, -1, 0, 1 );

				var shader = THREE.CopyShader;
				var uniforms = new THREE.UniformsUtils.clone( shader.uniforms );

				hudMaterial = new THREE.ShaderMaterial( { vertexShader: shader.vertexShader, fragmentShader: shader.fragmentShader, uniforms: uniforms } );

				var hudGeo = new THREE.PlaneGeometry( 2, 2 );
				var hudMesh = new THREE.Mesh( hudGeo, hudMaterial );

				sceneHUD = new THREE.Scene();
				sceneHUD.add( hudMesh );

				sceneHUD.add( cameraOrtho );

				hudMaterial.uniforms.tDiffuse.value = THREE.ImageUtils.loadTexture( "textures/vignette.png" );

			}

			function animate() {

				requestAnimationFrame( animate );

				render();
				stats.update();

			}

			function render() {

				var time = Date.now() * 0.00215;
				var delta = clock.getDelta();

				if ( soundtrack.readyState === soundtrack.HAVE_ENOUGH_DATA  && !playing ) {

					soundtrack.play();
					playing = true;
					timestamp = time;

				}

				for ( var c = 0; c < group.children.length; c ++ ) {

					var sprite = group.children[ c ];
					var scale = 1.0;

					var imageWidth = 1;
					var imageHeight = 1;

					if ( sprite.map && sprite.map.image && sprite.map.image.width ) {

						imageWidth = sprite.map.image.width;
						imageHeight = sprite.map.image.height;

					}

					sprite.scale.set( scale * imageWidth, scale * imageHeight, 1.0 );

					if ( playing && time > timestamp ) {

						var index = Math.floor( 4 * ( ( time + sprite.offset ) % 1 ) );
						sprite.map = frames[ index ];

						sprite.position.z += delta * 100;
						if ( sprite.position.z > 2000 ) sprite.position.z -= 6600;

						sprite.visible = true;

					}

					if ( !sprite.visible && Math.random() > 0.99 )
						sprite.visible = true;

				}

				if ( playing && time > timestamp )	{

					group.rotation.y += delta * 0.02;
					//group.position.z = Math.cos( time * 0.1 ) * 1000;

				}

				camera.lookAt( scene.position );

				//controls.update( 0.1 );

				renderer.clear();
				renderer.render( scene, camera );
				renderer.render( sceneHUD, cameraOrtho );

			}

		</script>
	</body>
</html>
