import Alpine from 'alpinejs'
import { initDropzone } from './alpine/dropzone'
import { initTinyMCE }  from './alpine/tinymce'
import { initCodeMirror } from './alpine/codemirror'

Alpine.data('voyagerDropzone', initDropzone)
Alpine.data('voyagerEditor',   initTinyMCE)
Alpine.data('voyagerCode',     initCodeMirror)

Alpine.start()
