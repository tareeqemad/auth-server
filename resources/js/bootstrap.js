import axios from 'axios';
import $ from 'jquery';
import toastr from 'toastr';
import 'toastr/build/toastr.css';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.css';
import Chart from 'chart.js/auto';

window.axios = axios;
window.$ = window.jQuery = $;
window.toastr = toastr;
window.Swal = Swal;
window.Chart = Chart;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const csrfToken = document.head.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': csrfToken.content },
    });
}

toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: 'toast-top-left',
    rtl: true,
    timeOut: 4000,
    extendedTimeOut: 1500,
    showMethod: 'slideDown',
    hideMethod: 'slideUp',
    newestOnTop: true,
};
