@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/dracula.min.css">
<style>
    .CodeMirror {
        height: auto;
        min-height: 520px;
        font-size: 13.5px;
        font-family: 'Fira Mono', 'Consolas', 'Menlo', monospace;
        border-radius: 0 0 6px 6px;
        border: 1px solid #dee2e6;
        border-top: none;
    }
    .CodeMirror-scroll { min-height: 520px; }
    .config-editor-toolbar {
        background: #2d2d3a;
        border-radius: 6px 6px 0 0;
        padding: 0.5rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .config-editor-toolbar .toolbar-title {
        color: #adb5bd;
        font-size: 0.8rem;
        letter-spacing: 0.04em;
        margin-right: auto;
    }
    #json-error-msg {
        display: none;
        border-left: 4px solid #e74a3b;
    }
    #json-valid-msg {
        display: none;
        border-left: 4px solid #1cc88a;
    }
</style>
@endpush

@section('content')
    <div class="section-header">
        <h1>{{ $viewData['title'] }}</h1>
        <div class="section-header-button">
            <a href="{{ route('admin.config.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-undo mr-1"></i> Reset changes
            </a>
        </div>
    </div>

    <div class="section-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div id="json-error-msg" class="alert alert-danger mb-3">
            <i class="fas fa-exclamation-circle mr-1"></i> <span id="json-error-text"></span>
        </div>
        <div id="json-valid-msg" class="alert alert-success mb-3">
            <i class="fas fa-check-circle mr-1"></i> JSON is valid.
        </div>

        <form id="config-form" method="POST" action="{{ route('admin.config.save') }}">
            @csrf
            <textarea name="config" id="config-textarea" class="d-none">{{ old('config', $json) }}</textarea>

            <div class="card shadow-sm mb-3">
                <div class="card-body p-0">
                    <div class="config-editor-toolbar">
                        <span class="toolbar-title"><i class="fas fa-code mr-1"></i> JSON Editor &mdash; App Config</span>
                        <button type="button" class="btn btn-outline-light btn-sm" id="btn-format">
                            <i class="fas fa-align-left mr-1"></i> Format
                        </button>
                        <button type="button" class="btn btn-outline-light btn-sm" id="btn-validate">
                            <i class="fas fa-check mr-1"></i> Validate
                        </button>
                    </div>

                    <div id="codemirror-wrapper"></div>
                </div>
            </div>

            @error('config')
                <div class="alert alert-danger mb-3">
                    <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                </div>
            @enderror

            <div class="d-flex flex-wrap gap-2 align-items-center">
                <button type="submit" class="btn btn-primary" id="btn-save">
                    <i class="fas fa-save mr-1"></i> Save Config
                </button>
                <a href="{{ route('admin.config.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times mr-1"></i> Cancel
                </a>
                <small class="text-muted ml-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    This JSON is served to the Android app via <code>GET /api/v1/app-config</code>.
                </small>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/matchbrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closebrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/fold/foldcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/fold/foldgutter.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/fold/brace-fold.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/lint/lint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/lint/json-lint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsonlint/1.6.0/jsonlint.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/fold/foldgutter.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/lint/lint.min.css">

<script>
(function () {
    var initialValue = document.getElementById('config-textarea').value;

    var editor = CodeMirror(document.getElementById('codemirror-wrapper'), {
        value: initialValue,
        mode: { name: 'javascript', json: true },
        theme: 'dracula',
        lineNumbers: true,
        matchBrackets: true,
        autoCloseBrackets: true,
        foldGutter: true,
        gutters: ['CodeMirror-linenumbers', 'CodeMirror-foldgutter', 'CodeMirror-lint-markers'],
        lint: true,
        indentUnit: 2,
        tabSize: 2,
        indentWithTabs: false,
        lineWrapping: false,
        extraKeys: {
            'Ctrl-S': function () { document.getElementById('config-form').submit(); },
            'Cmd-S': function () { document.getElementById('config-form').submit(); },
        }
    });

    function getEditorValue() {
        return editor.getValue();
    }

    function showError(msg) {
        var el = document.getElementById('json-error-msg');
        document.getElementById('json-error-text').textContent = msg;
        el.style.display = 'block';
        document.getElementById('json-valid-msg').style.display = 'none';
    }

    function showValid() {
        document.getElementById('json-valid-msg').style.display = 'block';
        document.getElementById('json-error-msg').style.display = 'none';
    }

    function hideMessages() {
        document.getElementById('json-valid-msg').style.display = 'none';
        document.getElementById('json-error-msg').style.display = 'none';
    }

    function tryParse(str) {
        try {
            return { ok: true, value: JSON.parse(str) };
        } catch (e) {
            return { ok: false, error: e.message };
        }
    }

    document.getElementById('btn-format').addEventListener('click', function () {
        var val = getEditorValue();
        var result = tryParse(val);
        if (!result.ok) {
            showError('Cannot format — invalid JSON: ' + result.error);
            return;
        }
        editor.setValue(JSON.stringify(result.value, null, 2));
        hideMessages();
    });

    document.getElementById('btn-validate').addEventListener('click', function () {
        var result = tryParse(getEditorValue());
        if (result.ok) {
            showValid();
        } else {
            showError(result.error);
        }
    });

    document.getElementById('config-form').addEventListener('submit', function (e) {
        var val = getEditorValue();
        var result = tryParse(val);
        if (!result.ok) {
            e.preventDefault();
            showError('Cannot save — invalid JSON: ' + result.error);
            return;
        }
        document.getElementById('config-textarea').value = val;
        hideMessages();
    });

    editor.on('change', function () { hideMessages(); });
})();
</script>
@endpush
