<template>
    <div>
        <vue-dropzone ref="myVueDropzone" id="dropzone" :options="dropzoneOptions" @vdropzone-complete="afterComplete" @vdropzone-sending="sendingEvent"></vue-dropzone>
    </div>
</template>

<script>
    import vue2Dropzone from 'vue2-dropzone'
    import 'vue2-dropzone/dist/vue2Dropzone.min.css'

    export default {
        components: {
            vueDropzone: vue2Dropzone
        },
        props: ['folder'],
        data() {
            return {
                dropzoneOptions: {
                    url: 'api/image',
                    maxFilesize: 10,
                    params: {
                        folder: this.folder
                    }
                    // autoProcessQueue: false,
                    // addRemoveLinks: true
                },
                resultFile : '',
            }
        },
        methods: {
            afterComplete(file) {
                if(file.xhr.status == 200) {
                    this.resultFile = JSON.parse(file.xhr.response)
                    // console.log(resultFile.filename);
                }
                this.$emit('fileToParent', this.resultFile.filename);
            },
            sendingEvent (file, xhr, formData) {
                formData.append('folder', this.folder);
            }
        },
        mounted() {
            console.log('Component mounted.')
        }
    }
</script>
