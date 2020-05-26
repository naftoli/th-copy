import { Camera } from '@ionic-native/camera';

document.addEventListener('deviceready', () => {
Camera.getPicture()
    .then(data => console.log('Took a picture!', data))
    .catch(e => console.log('Error occurred while taking a picture', e));
});