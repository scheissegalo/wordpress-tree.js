<?php
// your code

function render_url_threejs_scene($atts) {
    $a = shortcode_atts( array(
        'model_url' => 'https://example.com/wp-content/uploads/2023/08/3dmodel.gltf', //Default Model
		'link' => '\/web\/',  // Default Link
        'camera_z' => 300, // Default Camera Z Position (Distance)
        'camera_y' => 150, // Default Camera Y Position (Height)
        'camera_rotation_x' => -0.4, // Default Camera Angle (Point up or down)
        'rotation_speed' => 0.007, // Default object rotation speed
		'model_scale' => 1, // Default Model Scale
		'renderHeight' => 450, // Default Render div height
    ), $atts );

    ob_start(); // Start output buffering
    ?>
    <a href="<?php echo $a['link']; ?>">
		<div id="threejs-container" style="position: relative;"></div>
    </a>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
	<script src="https://unpkg.com/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
    <script>
        var scene, camera, renderer, customModel;
        var keyLight, fillLight, backLight;
		var loadingBar, loadingProgress;

        function init() {

            // Create loading bar elements
            loadingBar = document.createElement('div');
            loadingBar.id = 'loading-bar';
            loadingProgress = document.createElement('div');
            loadingProgress.id = 'loading-progress';
            loadingBar.appendChild(loadingProgress);
            loadingBar.innerHTML += 'Loading... 0%';
            document.getElementById('threejs-container').appendChild(loadingBar);

            // Create scene
            scene = new THREE.Scene();

            // Create camera with specified parameters
            camera = new THREE.PerspectiveCamera(50, window.innerWidth / <?php echo $a['renderHeight']; ?>, 0.1, 1000);
            camera.position.set(0, <?php echo $a['camera_y']; ?>, <?php echo $a['camera_z']; ?>);
            camera.rotation.x = <?php echo $a['camera_rotation_x']; ?>;
            scene.add(new THREE.AmbientLight(0x404040, 1));

            // Create lights
            keyLight = new THREE.DirectionalLight(0xffffff, 1.0);
            keyLight.position.set(-300, 300, 300);

            fillLight = new THREE.DirectionalLight(0xffffff, 0.75);
            fillLight.position.set(300, 0, 300);

            backLight = new THREE.DirectionalLight(0xffffff, 1.0);
            backLight.position.set(300, 0, -300);

            // Add lights to the scene
            scene.add(keyLight);
            scene.add(fillLight);
            scene.add(backLight);

            // Create renderer with transparent background
            renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
            renderer.setSize(window.innerWidth, <?php echo $a['renderHeight']; ?>);
            document.getElementById('threejs-container').appendChild(renderer.domElement);

            // Load custom 3D model
            var loader = new THREE.GLTFLoader();
            loader.load(
              '<?php echo $a['model_url']; ?>',
              function (gltf) {
                customModel = gltf.scene;
	      	customModel.scale.set(<?php echo $a['model_scale']; ?>, <?php echo $a['model_scale']; ?>, <?php echo $a['model_scale']; ?>); 
                scene.add(customModel);
              },
              function (xhr) {
                // Loading progress callback
                var percentLoaded = (xhr.loaded / xhr.total) * 100;
                loadingProgress.style.width = percentLoaded + '%';
                loadingBar.innerHTML = 'Loading... ' + Math.round(percentLoaded) + '%';
	      	loadingBar.appendChild(loadingProgress);
              },
              function (error) {
                console.error('Error loading model:', error);
                loadingBar.style.display = 'none'; // Hide loading bar on error
              }
            );

            // Observe container resizing
            const container = document.getElementById('threejs-container');
            const resizeObserver = new ResizeObserver(onContainerResize);
            resizeObserver.observe(container);
        }

        function onContainerResize(entries) {
            const { width, height } = entries[0].contentRect;
            camera.aspect = width / height;
            camera.updateProjectionMatrix();
            renderer.setSize(width, <?php echo $a['renderHeight']; ?>);
        }

        function animate() {
          requestAnimationFrame(animate);
          if (customModel) {
              customModel.rotation.y += <?php echo $a['rotation_speed']; ?>;
              loadingBar.style.display = 'none'; // Hide loading bar when model is loaded
          }
    	renderer.render(scene, camera);
        }

        init();
        animate();
    </script>
    <?php
    return ob_get_clean(); // Get the buffered output and return
}
add_shortcode('thee_render', 'render_url_threejs_scene');
