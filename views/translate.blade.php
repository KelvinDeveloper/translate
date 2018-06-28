<!DOCTYPE html>
<html>
<head>
    <title>Translate Manager</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
    <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <style>
        li {
            list-style: none;
            margin: 0 10px;
        }
        table {
            width: 100%;
            text-align: left;
        }
        table tr {
            height: 60px;
        }
        table tr td {
            vertical-align: top;
        }
        table select {
            float: left;
            height: 25px;
        }
        table tr:nth-child(even) {
            background-color: #f2f2f2
        }
        table tr .material-icons {
            color: #8e908c;
            cursor: default;
        }
        table tr:not(.pending) .material-icons {
            opacity: 0 !important;
        }

    </style>
</head>
<body>

<div class="translate-body">

    <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
            <span class="mdl-layout-title">Translate Manager</span>
            <div class="mdl-layout-spacer"></div>
            <nav class="mdl-navigation">
                <li>
                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--expandable">
                        <label class="mdl-button mdl-js-button mdl-button--icon" for="search">
                            <i class="material-icons">search</i>
                        </label>
                        <div class="mdl-textfield__expandable-holder">
                            <input class="mdl-textfield__input search" data-target="table" id="search" type="text">
                            <label class="mdl-textfield__label" for="sample-expandable">Expandable Input</label>
                        </div>
                    </div>
                </li>
                <li>
                    <button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored translate-save" disabled>Salvar</button>
                </li>
                <li>
                    <button id="demo-menu-lower-right" class="mdl-button mdl-js-button mdl-button--icon">
                        <i class="material-icons">more_vert</i>
                    </button>
                    <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="demo-menu-lower-right">
                        <li onclick="updateTexts()" class="mdl-menu__item">Atualizar termos</li>
                        <li onclick="autoTranslate()" class="mdl-menu__item">Traduzir automaticamente</li>
                        <li onclick="updateCache()" class="mdl-menu__item">Atualizar cache</li>
                    </ul>
                </li>
            </nav>
        </div>
    </header>

    <table class="mdl-data-table mdl-shadow--2dp">
        <thead>
        <th>{{ array_search(config('translate.default'), config('translate.languages')) }}</th>
        <th>
            <select>
                @foreach(config('translate.languages') as $lang => $code)
                    <option value="{{ $code }}" {{ $code == $translate_lang ? 'selected' : '' }}>{{ $lang }}</option>
                @endforeach
            </select>
        </th>
        <th></th>
        </thead>
        <tbody>
        @foreach(\Translate\Translate::orderBy('id_lang', 'ASC')->get(['id_lang', config('translate.default'), $translate_lang]) as $translate)
            <tr data-id="{{ $translate->id_lang }}" data-search="{{ strtolower( removeAccents ($translate->{config('translate.default')} . ' ' . $translate->$translate_lang) ) }}" data-value="{{ $translate->$translate_lang }}">
                <td>{{ $translate->{config('translate.default')} }}</td>
                <td>
                    <textarea class="mdl-textfield__input">{{ $translate->$translate_lang }}</textarea>
                </td>
                <td>
                    <i class="material-icons">save</i>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <dialog class="mdl-dialog">
        <div class="mdl-dialog__content"></div>
    </dialog>
</div>
<script>
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

    $('.translate-body select').change(function () {
        window.location.href = '/{{ config('translate.url_path') }}/manager/' + $(this).val();
    });
    $('.translate-body textarea').keyup(function () {
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
</script>
</body>
</html>