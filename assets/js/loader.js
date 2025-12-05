document.addEventListener('DOMContentLoaded', () => {
    const loadingProgress = document.getElementById('loading-progress');
    const loaderContainer = document.getElementById('loader-container');
    
    // --- 1. Loading Progress Bar Logic ---
    let progress = 0;
    const interval = setInterval(() => {
        progress += 10;
        loadingProgress.style.width = progress + '%';

        if (progress >= 100) {
            clearInterval(interval);
            
            // Fade out the loader and redirect
            loaderContainer.style.opacity = 0;
            setTimeout(() => {
                // Redirect to the main index page
                window.location.href = 'index.php'; 
            }, 1500); // Wait 1.5 seconds for the fade-out CSS transition
        }
    }, 200);

    // --- 2. 3D Droplet Setup (Three.js) ---
    
    // Standard setup
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(75, 1, 0.1, 1000);
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    
    const canvas = document.getElementById('threejs-canvas');
    renderer.setSize(300, 300);
    renderer.setClearColor(0x000000, 0); // Transparent background
    canvas.appendChild(renderer.domElement);

    // Define the shape profile for the LatheGeometry (Droplet shape)
    const points = [];
    // Start with a smooth curve for the bottom body
    for (let i = 0; i < 10; i++) {
        // Creates the curved base and side
        points.push(new THREE.Vector2(Math.sin(i * 0.2) * 1 + 0.1, -(i * 0.5)));
    }
    // Add the final tip point (bottom of the droplet)
    points.push(new THREE.Vector2(0, -5));
    // Close the top of the shape
    points.push(new THREE.Vector2(0.01, 0));

    // Create the geometry by revolving the profile points around the Y-axis
    const geometry = new THREE.LatheGeometry(points, 32); 

    // Material: Deep, shiny, and slightly translucent red
    const material = new THREE.MeshPhongMaterial({ 
        color: 0x990000,          // Deep blood red
        specular: 0xffffff,       // White specular highlight
        shininess: 80,            // High shine
        transparent: true,        // Enable transparency
        opacity: 0.9,             // Slight translucency
        flatShading: false
    });
    
    const bloodDroplet = new THREE.Mesh(geometry, material);
    scene.add(bloodDroplet);
    
    // Position the droplet to be centered and properly scaled
    bloodDroplet.position.y = 2.5;
    camera.position.z = 10; 

    // Lighting (Crucial for 3D visibility)
    const keyLight = new THREE.DirectionalLight(0xffffff, 1.5);
    keyLight.position.set(5, 5, 5).normalize();
    scene.add(keyLight);
    
    const fillLight = new THREE.DirectionalLight(0xffffff, 0.5);
    fillLight.position.set(-5, -5, -5).normalize();
    scene.add(fillLight);
    
    scene.add(new THREE.AmbientLight(0x404040)); // Soft ambient light

    // Animation Loop
    function animate() {
        requestAnimationFrame(animate);
        // Subtle, continuous rotation
        bloodDroplet.rotation.y += 0.015; 
        bloodDroplet.rotation.z += 0.005; 
        renderer.render(scene, camera);
    }
    animate();
});