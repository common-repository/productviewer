document.addEventListener('DOMContentLoaded',()=>{

    let viewerButtonClicked = false
    

    
    const canvasID = document.querySelector('.canvas_admin_viewer')
    
    let modelUrl = document.querySelector('#model_url')
    const sizes = {
        width:250,
        height: 150
      }
    let allBoxs = document.querySelectorAll('.color_product_viewer_box_single')
    const gltfLoad =  new GLTFLoader()
    const fbxLoad =  new FBXLoader()
    const scene = new THREE.Scene()
    
    let renderer = new THREE.WebGLRenderer({canvas: canvasID ,antialias: true,alpha:true})
 
    renderer.setSize(sizes.width,sizes.height)
    //set background color
    renderer.setClearColor('#fff',1 );
    
    let models = []
 
   let lis = []


   setTimeout(() => {
       for (let i = 0; i < lis.length; i++) {
        lis[i].addEventListener('click',()=>{
            for (let d = 0; d < lis.length; d++) {
                lis[d].style.color ='white'
            }
            lis[i].style.color = 'black'
           
        })
           
       }
   }, 1000);

   function addChilds(){
    models[0].children.forEach(c=>{
        if(c.type == 'Mesh') {
            let li = document.createElement('li')
            li.innerHTML = c.name

            li.addEventListener('click',()=>{
                c.material.color.set( 0xff0000 )
                document.querySelector('#model_part_color').value = c.name
            })
            if(document.querySelector('#model_part_color').value !== undefined){
                if(c.name === document.querySelector('#model_part_color').value){
                    li.style.color ='black'
                }
            }

       
             
            lis.push(li)


            document.querySelector('.viewer_model_childs').appendChild(li)
        }
    })
   }

    
    function addGltf(url){
        gltfLoad.load(url,(gltf) =>{
            gltf.scene.scale.x = 1
            gltf.scene.scale.y = 1
            gltf.scene.scale.z = 1
            models.push(gltf.scene)
            scene.add(gltf.scene)
            addChilds()

    
        })
    }
    
    function addFbx(url){
        fbxLoad.load(url,(fbx) =>{
           fbx.scale.x = 1
           fbx.scale.y = 1
           fbx.scale.z = 1
            scene.add(fbx)
            models.push(fbx)
            addChilds()
          
    
        })
    }
    
    if(modelUrl.value.includes('gltf') || modelUrl.value.includes('glb'))
    {
        addGltf(`${modelUrl.value}`)
        
    
    }
    else if(modelUrl.value.includes('fbx'))
    {
        addFbx(`${modelUrl.value}`)
        
    }

    modelUrl.addEventListener('input',()=>{
        if(modelUrl.value.includes('gltf') || modelUrl.value.includes('glb'))
        {
            addGltf(`${modelUrl.value}`)
        
        }
        else if(modelUrl.value.includes('fbx'))
        {
            addFbx(`${modelUrl.value}`)
        }
        else{
            scene.remove(models[0])
            models.splice(0,1)
        }
        
    })
   
   

    
    const rgb2hex = (rgb) => `#${rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/).slice(1).map(n => parseInt(n, 10).toString(16).padStart(2, '0')).join('')}`


 let colors = []
  let checks = document.querySelectorAll('.checks')




    allBoxs.forEach((b,i)=>{

        b.addEventListener('click',()=>{
            b.style.opacity = '.5'

            let hexVal = rgb2hex(window.getComputedStyle(b).backgroundColor)
            if(!colors.includes(hexVal)){
                colors.push(hexVal)
                document.querySelector('#model_colors').value  = colors
             
    
     
            }
            
        })

     

    })
    
    
    const aspectRatio = sizes.width/sizes.height
    // camera
    const camera = new THREE.PerspectiveCamera(75,aspectRatio,1,2000)
    
    scene.add(camera)
    
    camera.position.z = 10
    
    

        
    const controls = new THREE.OrbitControls(
        camera, canvasID);
        controls.enableDamping = true
    
      
    
        const light = new THREE.AmbientLight( 0x404040,1.75 ); // soft white light
        scene.add( light );
    
        const clock = new THREE.Clock()
    
    
        //render animation
        function anim(){
          // time of clock
            requestAnimationFrame(anim)
            renderer.render(scene,camera)
            const elpasedTime = clock.getElapsedTime()
        
        
        
        
        }
        
        anim()
    
    
     

    
    
    })