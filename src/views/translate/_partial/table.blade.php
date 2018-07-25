<table id="translate-table" class="display" style="display: none">
    <thead>
        <tr>
            <th class="no-sort">Revisado</th>
            <th class="no-sort">{{ array_search(config('translate.default'), config('translate.languages')) }}</th>
            <th class="no-sort">
                <select>
                    @foreach(config('translate.languages') as $lang => $code)
                        <option value="{{ $code }}" {{ $code == $translate_lang ? 'selected' : '' }}>{{ $lang }}</option>
                    @endforeach
                </select>
            </th>
            <th class="no-sort"></th>
        </tr>
    </thead>
    <tbody>
    @foreach(\Translate\Translate::orderBy('id_lang', 'ASC')->get(['id_lang', config('translate.default'), $translate_lang]) as $translate)
        <tr data-id="{{ $translate->id_lang }}" data-search="{{ strtolower( removeAccents ($translate->{config('translate.default')} . ' ' . $translate->$translate_lang) ) }}" data-value="{{ $translate->$translate_lang }}">
            <td style="width: 50px">
                <input onchange="verified($(this))" type="checkbox" id="switch-{{ $translate->id_lang }}" {{ in_array($translate->id_lang, $verify) ? 'checked' : '' }}>
            </td>
            <td style="width: 25%">{{ $translate->{config('translate.default')} }}</td>
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