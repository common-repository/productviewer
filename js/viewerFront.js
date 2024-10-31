
document.addEventListener('DOMContentLoaded',()=>{

const viewerButton = document.querySelector('.model_viewer_product_button')
let modelContainer =  document.querySelector('#model_viewer_product_container')
let viewerButtonClicked = false






const canvasID = document.querySelector('.viewer_canvas')

const modelUrl = modelContainer.getAttribute('data-model')
const modelDisplay = modelContainer.getAttribute('data-display')
const modelDisplayThumbnail = modelContainer.getAttribute('data-inst')
const modelColors = document.querySelector('.color_product_viewer_box').getAttribute('data-colors')
const modelPart = document.querySelector('.color_product_viewer_box').getAttribute('data-part')



let arr = modelColors.split(',')
console.log(arr)

arr.forEach((r,i)=>{
   let div = document.createElement('div')
   div.setAttribute('class','color_product_viewer_box_single')
   div.style.backgroundColor = r
   document.querySelector('.color_product_viewer_box').appendChild(div)
})

let allColors = document.querySelectorAll('.color_product_viewer_box_single')
let lastColorID = 0



if(modelDisplay === 'yes' && modelDisplayThumbnail !== 'yes'){
    viewerButton.addEventListener('click',viewerOpen)
}





const rgb2hex = (rgb) => `#${rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/).slice(1).map(n => parseInt(n, 10).toString(16).padStart(2, '0')).join('')}`


const gltfLoad =  new GLTFLoader()
const fbxLoad =  new FBXLoader()
const scene = new THREE.Scene()

let renderer = new THREE.WebGLRenderer({canvas: canvasID ,antialias: true,alpha:true})
const sizes = {
    width: 650,
    height: 500
  
  
  }
renderer.setSize(sizes.width,sizes.height)
//set background color
renderer.setClearColor('#fff',1 );

let models = []


function addGltf(url){
    gltfLoad.load(url,(gltf) =>{
        gltf.scene.scale.x = 1
        gltf.scene.scale.y = 1
        gltf.scene.scale.z = 1
        models.push(gltf.scene)
        scene.add(gltf.scene)
        
   
    })
}

function addFbx(url){
    fbxLoad.load(url,(fbx) =>{
       fbx.scale.x = 1
       fbx.scale.y = 1
       fbx.scale.z = 1
        scene.add(fbx)
        models.push(fbx)

    })
}


if(modelDisplay === 'yes')
{
    if(modelUrl.includes('gltf') || modelUrl.includes('glb'))
{
    addGltf(`${modelUrl}`)

}
else if(modelUrl.includes('fbx'))
{
    addFbx(`${modelUrl}`)
}



}


const aspectRatio = sizes.width/sizes.height
// camera
const camera = new THREE.PerspectiveCamera(75,aspectRatio,1,2000)

scene.add(camera)

camera.position.z = 10



	
const controls = new THREE.OrbitControls(
    camera, canvasID);
    controls.enableDamping = true

    // controls.addEventListener( 'change',()=>{

    //   disableWheel = false
    // })

    const light = new THREE.AmbientLight( 0x404040,1.75 ); // soft white light
    scene.add( light );

    const clock = new THREE.Clock()


    //render animation
    function anim(){
      // time of clock
        requestAnimationFrame(anim)
        renderer.render(scene,camera)
        const elpasedTime = clock.getElapsedTime()
    
         

         if(modelDisplay === 'yes' || modelDisplayThumbnail === 'yes'){

            
          document.querySelector('.credit_viewer_front').innerHTML ='Powerd by MapleWP'

          document.querySelector('.credit_viewer_front').style.zIndex = '555555'
          document.querySelector('.credit_viewer_front').style.display = 'block'
          document.querySelector('.credit_viewer_front').style.opacity = '1'
            allColors.forEach((c,i)=>{
  
                if(lastColorID == i) c.style.opacity = .5
                else c.style.opacity = 1
             c.addEventListener('click',()=>{
                 lastColorID = i
                 let hexVal = rgb2hex(window.getComputedStyle(c).backgroundColor)
         
                 document.querySelector('#product_viewer_color_select').value = hexVal
                 
                 models[0].children.forEach(c=>{
                    if(c.name === modelPart ){
                        c.material.color.set( new THREE.Color(hexVal) )
    
                    }
                })
                   
             })
           
           
         })
         }
   
     
    }
    
    anim()
    setTimeout(() => {
        sizes.width = document.querySelector('.woocommerce-product-gallery__wrapper').getBoundingClientRect().width-15
        sizes.height = document.querySelector('.woocommerce-product-gallery__wrapper').getBoundingClientRect().height-15

        renderer.setSize(sizes.width,sizes.height)
    }, 10);
 

function viewerOpen(){
    
    if(viewerButtonClicked === false)
    {
        
 
        viewerButtonClicked = true
        modelContainer.style.display = 'block'
        viewerButton.innerHTML = 'Close 3D'
        setTimeout(() => {
            sizes.width = document.querySelector('.woocommerce-product-gallery__wrapper').getBoundingClientRect().width-15
            sizes.height = document.querySelector('.woocommerce-product-gallery__wrapper').getBoundingClientRect().height-15

            renderer.setSize(sizes.width,sizes.height)
        }, 10);
     

    }
    else{
        viewerButtonClicked = false
        modelContainer.style.display = 'none'
        viewerButton.innerHTML = 'Preview 3D'

    }
}



})