import axios from 'axios';
import * as coreui from '@coreui/coreui'

window.coreui = coreui
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
