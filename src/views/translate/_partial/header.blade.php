<header class="mdl-layout__header">
    <div class="mdl-layout__header-row">
        <div class="mdl-layout-spacer"></div>
        <button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored translate-save" disabled>Salvar</button>
    </div>
</header>

<div class="options">
    <label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="option-2" style="margin-right: 5px;">
        <input type="radio" id="option-2" class="mdl-radio__button" name="options" value="2" checked onchange="showing($(this))">
        <span class="mdl-radio__label">Não revisados</span>
    </label>

    <label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="option-1">
        <input type="radio" id="option-1" class="mdl-radio__button" name="options" value="1" checked onchange="showing($(this))">
        <span class="mdl-radio__label">Tudo</span>
    </label>
</div>