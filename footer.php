    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="<?php  echo BASE_URL ?>/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="<?php  echo BASE_URL ?>/assets/vendor/libs/popper/popper.js"></script>
    <script src="<?php  echo BASE_URL ?>/assets/vendor/js/bootstrap.js"></script>
    <script src="<?php  echo BASE_URL ?>/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="<?php  echo BASE_URL ?>/assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="<?php  echo BASE_URL ?>/assets/vendor/libs/apex-charts/apexcharts.js"></script>

    <!-- Main JS -->
    <script src="<?php  echo BASE_URL ?>/assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="<?php  echo BASE_URL ?>/assets/js/dashboards-analytics.js"></script>

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>


    <!-- DataTables JS -->
    <!-- Inclua no final da página (antes de </body>) -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- Exportação -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="<?php  echo BASE_URL ?>/js/chat.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <script>
        $(document).ready(function() {
        $('#tabelaResultados').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
            { extend: 'copy', className: 'btn btn-outline-secondary' },
            { extend: 'csv', className: 'btn btn-outline-secondary' },
            { extend: 'excel', className: 'btn btn-success' },
            { extend: 'print', className: 'btn btn-outline-secondary' }
            ],
            language: {
            search: "Pesquisar:",
            lengthMenu: "Mostrar _MENU_ registros por página",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            paginate: {
                first: "Primeiro",
                last: "Último",
                next: "Próximo",
                previous: "Anterior"
            },
            buttons: {
                copy: "Copiar",
                csv: "CSV",
                excel: "Excel",
                print: "Imprimir"
            }
            },
            columnDefs: [
            { targets: [2, 3], className: 'text-wrap' },
            { targets: '_all', className: 'align-middle' }
            ]
        });

        });

        function toggleChat() {
            const chat = document.getElementById('chat-container');
            chat.classList.toggle('d-none');
        }

        new TomSelect("#select-atleta, #select-info", {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            },
            maxOptions: 1000, // caso tenha muitos atletas
            persist: false,
            searchField: ["text"],
            placeholder: "Digite para buscar..."
        });

    </script>