<!DOCTYPE html>
<html>
<head>
    <title>Translate Manager</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
    <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <style>
        .left {
            float: left;
            margin-right: 15px;
        }
        .right {
            float: right;
            margin-left: 15px;
        }
        .mdl-layout__drawer .mdl-navigation .mdl-navigation__link {
            padding: 10px !important;
        }
        .mdl-layout__drawer>.mdl-layout__title, .mdl-layout__drawer>.mdl-layout-title {
            line-height: 36px;
            padding: 10px !important;
            text-align: center;
            margin-top: 10px;
            border-bottom: solid 1px #dad8d8;
        }
        table.dataTable thead .no-sort.sorting_asc {
            background-image: none !important;
            cursor: default !important;
        }
        table select {
            width: 100%;
        }
        select, input {
            font-size: 14px;
            height: 30px;
            background-color: #fff;
        }
        table tr .material-icons {
            color: #8e908c;
            cursor: default;
        }
        table tr:not(.pending) .material-icons {
            opacity: 0 !important;
        }
        .options {
            padding: 10px;
            text-align: right;
        }
        .translate-table_wrapper {
            padding: 5px;
        }
    </style>
</head>
<body>
<!-- The drawer is always open in large screens. The header is always shown,
  even in small screens. -->
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-drawer mdl-layout--fixed-header">
    {!! view('translate._partial.header') !!}
    {!! view('translate._partial.menu') !!}
    <main class="mdl-layout__content" style="padding-top: 15px">
        <div class="page-content">
            {!! view('translate._partial.table', compact('translate_lang', 'verify')) !!}
        </div>
    </main>
    <dialog class="mdl-dialog">
        <div class="mdl-dialog__content"></div>
    </dialog>
</div>

<script>
    function showing(This) {
        if (This.val() == 1) {
            return $('table tr').show();
        }

        $('table tr [type="checkbox"]:checked').each(function () {
            $(this).parents('tr').hide();
        });
    }
    function removeAccents(varString) {
        if (varString == null) {
            return false;
        }
        varString = String(varString);
        var stringAcentos = new String('áàâãèêéíìîóõòôúûùçÁÀÃÂÉÈÊÍÌÎÔÓÕÒÚÛÙÇ'),
            stringSemAcento = new String('aaaaeeeiiioooouuucAAAAEEEIIIOOOOUUUC'),
            i = new Number(),
            j = new Number(),
            cString = new String(),
            varRes = '';
        for (i = 0; i < varString.length; i++) {
            cString = varString.substring(i, i + 1);
            for (j = 0; j < stringAcentos.length; j++) {

                if (stringAcentos.substring(j, j + 1) == cString) {
                    cString = stringSemAcento.substring(j, j + 1);
                }
            }
            varRes += cString;
        }
        return varRes;
    }
    function autoTranslate() {
        $('.mdl-dialog__content').html(
            'Traduzindo termos, por favor, aguarde... <br>' +
            '<div id="p2" class="mdl-progress mdl-js-progress mdl-progress__indeterminate"></div>'
        );
        document.querySelector('dialog').showModal();
        $.ajax({
            url: '/{{ config('translate.url_path') }}/manager/auto_translate',
            type: 'post',
            data: {
                _token: '{{ csrf_token() }}',
                language: '{{ $translate_lang }}'
            },
            complete: function () {
                location.reload();
            }
        });
    }

    function updateTexts() {

        $('.mdl-dialog__content').html(
            'Atualizando termos, por favor, aguarde... <br>' +
            '<div id="p2" class="mdl-progress mdl-js-progress mdl-progress__indeterminate"></div>'
        );
        document.querySelector('dialog').showModal();
        $.ajax({
            url: '/{{ config('translate.url_path') }}/manager/update_texts',
            type: 'post',
            data: {
                _token: '{{ csrf_token() }}',
            },
            complete: function () {
                location.reload();
            }
        });
    }

    function updateCache() {

        $('.mdl-dialog__content').html(
            'Atualizando cache, por favor, aguarde... <br>' +
            '<div id="p2" class="mdl-progress mdl-js-progress mdl-progress__indeterminate"></div>'
        );
        document.querySelector('dialog').showModal();
        $.ajax({
            url: '/{{ config('translate.url_path') }}/manager/update_cache',
            type: 'post',
            data: {
                _token: '{{ csrf_token() }}',
            },
            complete: function () {
                location.reload();
            }
        });
    }

    function verified(This) {
        $.ajax({
            url: '/{{ config('translate.url_path') }}/manager/verified',
            type: 'post',
            data: {
                _token: '{{ csrf_token() }}',
                id_lang: This.parents('tr').attr('data-id'),
                language: '{{ $translate_lang }}',
                check: This.prop('checked')
            }
        });
    }

    function changeLanguage (This) {
        window.location.href = '/{{ config('translate.url_path') }}/manager/' + This.val();
    };
    $(document).on('keyup', 'input.search', function (e) {

        var Query   = removeAccents($(this).val().toLowerCase()),
            Display = '';

        if (typeof $(this).attr('data-display') != 'undefined' ) {

            Display = $(this).attr('data-display');
        }

        if (Query == '') {

            $($(this).attr('data-target') + ' [data-search]').css('display', Display);
        } else {

            $($(this).attr('data-target') + ' [data-search]').hide();
            $($(this).attr('data-target') + ' [data-search*="' + Query + '"]').show().parents(':hidden').show();
        }
    });
    $('.translate-save:not(disabled)').click(function () {

        $('.mdl-dialog__content').html(
            'Salvando registros, por favor, aguarde... <br>' +
            '<div id="p1" class="mdl-progress mdl-js-progress"></div>'
        );
        document.querySelector('dialog').showModal();

        var total    = $('tr.pending').length,
            progress = 0,
            errors   = 0;

        $('tr.pending').each(function () {

            $.ajax({
                url: '/{{ config('translate.url_path') }}/manager/update',
                type: 'post',
                data: {
                    _token: '{{ csrf_token() }}',
                    id_lang: $(this).attr('data-id'),
                    '{{ $translate_lang }}': $(this).find('textarea').val(),
                    language: '{{ $translate_lang }}'
                },
                complete: function () {

                    progress++;

                    percent = (progress/total) * 100;

                    $('.progressbar').width( percent > 100 ? 100 : percent + '%');

                    if (total == progress) {
                        if (errors == 0) {
                            $('.mdl-dialog__content').html('Salvo com sucesso!');
                            setTimeout(function () {
                                location.reload();
                            }, 2000);
                        } else {
                            $('.mdl-dialog__content').html('Não foi possível salvar ' + errors + ' registro(s).<br> A página será atualizada em alguns segundos...');
                            setTimeout(function () {
                                location.reload();
                            }, 5000);
                        }
                    }
                },
                error: function () {
                    errors++;
                }
            });
        });
    });
    $(document).ready(function () {

        $('#translate-table').DataTable({
            columnDefs: [
                { targets: 'no-sort', orderable: false }
            ],
            initComplete: function () {
                $('#translate-table').show();
            },
            pageLength: 100,
            language: {
                "sEmptyTable": "Nenhum registro encontrado",
                "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                "sInfoPostFix": "",
                "sInfoThousands": ".",
                "sLengthMenu": "_MENU_ resultados por página",
                "sLoadingRecords": "Carregando...",
                "sProcessing": "Processando...",
                "sZeroRecords": "Nenhum registro encontrado",
                "sSearch": "Pesquisar",
                "oPaginate": {
                    "sNext": "Próximo",
                    "sPrevious": "Anterior",
                    "sFirst": "Primeiro",
                    "sLast": "Último"
                },
                "oAria": {
                    "sSortAscending": ": Ordenar colunas de forma ascendente",
                    "sSortDescending": ": Ordenar colunas de forma descendente"
                }
            }
        });

        $(document).off('keyup', '#translate-table textarea');
        $(document).on('keyup', '#translate-table textarea', function () {
            if ($(this).val() != $(this).parents('tr').attr('data-value')) {
                $(this).parents('tr').addClass('pending');
            } else {
                $(this).parents('tr').removeClass('pending');
            }

            if ($('tr.pending').length > 0) {
                $('.translate-save:not(disabled)').removeAttr('disabled');
            } else {
                $('.translate-save:not(disabled)').attr('disabled', 'disabled');
            }
        });
    });
</script>
</body>
</html>