@extends('layouts.app')

@section('content')

<div class="container py-5">

    <div class="text-center mb-4">
        <h2 class="fw-bold">Protect Your Evidence</h2>
        <p class="text-muted">
            Hide private information before submitting your evidence.
            Select a redaction tool and drag over sensitive areas.
        </p>
    </div>

    {{-- Upload --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <label for="imageInput" class="form-label fw-bold">
                Upload Evidence
            </label>

            <input
                type="file"
                id="imageInput"
                accept="image/*"
                class="form-control"
            >

        </div>
    </div>

    {{-- Toolbar --}}
    <div id="editorSection" style="display:none;">

        <div class="card shadow-sm mb-3">
            <div class="card-body">

                <div class="d-flex flex-wrap align-items-center gap-2">

                    <span class="fw-bold me-2">
                        Redaction:
                    </span>

                    <button
                        type="button"
                        class="btn btn-dark tool-btn active"
                        data-tool="blackout"
                    >
                        Blackout
                    </button>

                    <button
                        type="button"
                        class="btn btn-outline-secondary tool-btn"
                        data-tool="blur"
                    >
                        Blur
                    </button>

                    <button
                        type="button"
                        class="btn btn-outline-secondary tool-btn"
                        data-tool="pixelate"
                    >
                        Pixelate
                    </button>

                    <div class="vr mx-2"></div>

                    <button
                        type="button"
                        id="undoBtn"
                        class="btn btn-outline-secondary"
                        disabled
                    >
                        Undo
                    </button>

                    <button
                        type="button"
                        id="redoBtn"
                        class="btn btn-outline-secondary"
                        disabled
                    >
                        Redo
                    </button>

                    <button
                        type="button"
                        id="clearBtn"
                        class="btn btn-outline-danger"
                    >
                        Clear All
                    </button>

                </div>

            </div>
        </div>

        {{-- Zoom --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body">

                <div class="d-flex align-items-center gap-2">

                    <button
                        type="button"
                        id="zoomOutBtn"
                        class="btn btn-outline-secondary"
                    >
                        −
                    </button>

                    <span id="zoomLabel" class="fw-bold">
                        100%
                    </span>

                    <button
                        type="button"
                        id="zoomInBtn"
                        class="btn btn-outline-secondary"
                    >
                        +
                    </button>

                    <button
                        type="button"
                        id="resetZoomBtn"
                        class="btn btn-outline-secondary ms-2"
                    >
                        Reset Zoom
                    </button>

                </div>

            </div>
        </div>

        {{-- Instructions --}}
        <div class="alert alert-light border">
            <strong>How to redact:</strong>
            Click and drag over any private information such as names,
            email addresses, phone numbers, usernames, or other sensitive data.
        </div>

        {{-- Canvas --}}
        <div
            id="canvasWrapper"
            class="border rounded bg-light p-2"
            style="overflow:auto; max-height:70vh;"
        >
            <canvas
                id="canvas"
                style="
                    display:block;
                    margin:auto;
                    cursor:crosshair;
                "
            ></canvas>
        </div>

        {{-- Submit --}}
        <form
            method="POST"
            action="{{ route('incident.wizard.postRedact') }}"
            id="redactionForm"
        >
            @csrf

            <input
                type="hidden"
                name="redacted_image"
                id="redacted_image"
            >

            <div class="d-flex justify-content-end gap-2 mt-3">

                <button
                    type="button"
                    id="previewBtn"
                    class="btn btn-outline-primary"
                >
                    Preview
                </button>

                <button
                    type="submit"
                    class="btn btn-danger"
                >
                    Apply Redaction & Continue
                </button>

            </div>
        </form>

    </div>

</div>


{{-- Preview Modal --}}
<div
    class="modal fade"
    id="previewModal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Redacted Evidence Preview
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>
            </div>

            <div class="modal-body text-center">

                <img
                    id="previewImage"
                    src=""
                    alt="Redacted preview"
                    class="img-fluid rounded"
                >

            </div>

        </div>
    </div>
</div>


<script>

const input = document.getElementById('imageInput');
const canvas = document.getElementById('canvas');
const ctx = canvas.getContext('2d');

const hiddenInput = document.getElementById('redacted_image');
const editorSection = document.getElementById('editorSection');

const undoBtn = document.getElementById('undoBtn');
const redoBtn = document.getElementById('redoBtn');
const clearBtn = document.getElementById('clearBtn');

const zoomInBtn = document.getElementById('zoomInBtn');
const zoomOutBtn = document.getElementById('zoomOutBtn');
const resetZoomBtn = document.getElementById('resetZoomBtn');
const zoomLabel = document.getElementById('zoomLabel');

const previewBtn = document.getElementById('previewBtn');
const previewImage = document.getElementById('previewImage');

let image = new Image();

let baseCanvas = document.createElement('canvas');
let baseCtx = baseCanvas.getContext('2d');

let drawing = false;

let startX = 0;
let startY = 0;

let currentX = 0;
let currentY = 0;

let activeTool = 'blackout';

let zoom = 1;

let redactions = [];

let undoStack = [];
let redoStack = [];

let previewRedaction = null;


/* ---------------------------------------------------
   IMAGE UPLOAD
--------------------------------------------------- */

input.addEventListener('change', function (event) {

    const file = event.target.files[0];

    if (!file) {
        return;
    }

    const reader = new FileReader();

    reader.onload = function (e) {

        image.onload = function () {

            baseCanvas.width = image.width;
            baseCanvas.height = image.height;

            canvas.width = image.width;
            canvas.height = image.height;

            baseCtx.clearRect(
                0,
                0,
                baseCanvas.width,
                baseCanvas.height
            );

            baseCtx.drawImage(
                image,
                0,
                0
            );

            redactions = [];

            undoStack = [];
            redoStack = [];

            zoom = 1;

            updateZoom();

            redraw();

            updateHistoryButtons();

            editorSection.style.display = 'block';

        };

        image.src = e.target.result;

    };

    reader.readAsDataURL(file);

});
// ---------------------------------------------------
// LOAD IMAGE FROM STEP 3
// ---------------------------------------------------

const storedImage =
    sessionStorage.getItem('incident_evidence_image');

if (storedImage) {

    image.onload = function () {

        baseCanvas.width = image.width;
        baseCanvas.height = image.height;

        canvas.width = image.width;
        canvas.height = image.height;

        baseCtx.clearRect(
            0,
            0,
            baseCanvas.width,
            baseCanvas.height
        );

        baseCtx.drawImage(
            image,
            0,
            0
        );

        redactions = [];
        undoStack = [];
        redoStack = [];

        zoom = 1;

        updateZoom();
        redraw();
        updateHistoryButtons();

        editorSection.style.display = 'block';
    };

    image.src = storedImage;

}

/* ---------------------------------------------------
   TOOL SELECTION
--------------------------------------------------- */

document.querySelectorAll('.tool-btn').forEach(button => {

    button.addEventListener('click', function () {

        document.querySelectorAll('.tool-btn').forEach(btn => {

            btn.classList.remove('active');

        });

        this.classList.add('active');

        activeTool = this.dataset.tool;

    });

});


/* ---------------------------------------------------
   CANVAS COORDINATES
--------------------------------------------------- */

function getCanvasCoordinates(event) {

    const rect = canvas.getBoundingClientRect();

    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;

    let clientX;
    let clientY;

    if (event.touches && event.touches.length > 0) {

        clientX = event.touches[0].clientX;
        clientY = event.touches[0].clientY;

    } else {

        clientX = event.clientX;
        clientY = event.clientY;

    }

    return {

        x: (clientX - rect.left) * scaleX,
        y: (clientY - rect.top) * scaleY

    };

}


/* ---------------------------------------------------
   DRAW START
--------------------------------------------------- */

function startDrawing(event) {

    event.preventDefault();

    if (!image.src) {
        return;
    }

    const point = getCanvasCoordinates(event);

    startX = point.x;
    startY = point.y;

    currentX = startX;
    currentY = startY;

    drawing = true;

}


/* ---------------------------------------------------
   DRAW MOVE
--------------------------------------------------- */

function moveDrawing(event) {

    if (!drawing) {
        return;
    }

    event.preventDefault();

    const point = getCanvasCoordinates(event);

    currentX = point.x;
    currentY = point.y;

    redraw();

    previewRedaction = {
        x: startX,
        y: startY,
        width: currentX - startX,
        height: currentY - startY,
        tool: activeTool
    };

    drawRedaction(
        previewRedaction,
        true
    );

}


/* ---------------------------------------------------
   DRAW END
--------------------------------------------------- */

function finishDrawing(event) {

    if (!drawing) {
        return;
    }

    event.preventDefault();

    drawing = false;

    const width = currentX - startX;
    const height = currentY - startY;

    if (
        Math.abs(width) < 5 ||
        Math.abs(height) < 5
    ) {

        previewRedaction = null;

        redraw();

        return;

    }

    saveState();

    redactions.push({

        x: startX,
        y: startY,
        width: width,
        height: height,
        tool: activeTool

    });

    previewRedaction = null;

    redoStack = [];

    redraw();

    updateHistoryButtons();

    saveImage();

}


/* ---------------------------------------------------
   MOUSE EVENTS
--------------------------------------------------- */

canvas.addEventListener(
    'mousedown',
    startDrawing
);

canvas.addEventListener(
    'mousemove',
    moveDrawing
);

canvas.addEventListener(
    'mouseup',
    finishDrawing
);

canvas.addEventListener(
    'mouseleave',
    finishDrawing
);


/* ---------------------------------------------------
   TOUCH EVENTS
--------------------------------------------------- */

canvas.addEventListener(
    'touchstart',
    startDrawing,
    { passive:false }
);

canvas.addEventListener(
    'touchmove',
    moveDrawing,
    { passive:false }
);

canvas.addEventListener(
    'touchend',
    finishDrawing,
    { passive:false }
);


/* ---------------------------------------------------
   REDRAW
--------------------------------------------------- */

function redraw() {

    ctx.clearRect(
        0,
        0,
        canvas.width,
        canvas.height
    );

    ctx.drawImage(
        baseCanvas,
        0,
        0
    );

    redactions.forEach(redaction => {

        drawRedaction(
            redaction,
            false
        );

    });

}


/* ---------------------------------------------------
   DRAW REDACTION
--------------------------------------------------- */

function drawRedaction(redaction, preview = false) {

    let x = redaction.x;
    let y = redaction.y;

    let width = redaction.width;
    let height = redaction.height;

    if (width < 0) {

        x += width;
        width = Math.abs(width);

    }

    if (height < 0) {

        y += height;
        height = Math.abs(height);

    }

    if (redaction.tool === 'blackout') {

        ctx.fillStyle = preview
            ? 'rgba(0,0,0,0.65)'
            : 'black';

        ctx.fillRect(
            x,
            y,
            width,
            height
        );

    }

    else if (redaction.tool === 'blur') {

        const temp = document.createElement('canvas');

        temp.width = width;
        temp.height = height;

        const tempCtx = temp.getContext('2d');

        tempCtx.filter = 'blur(10px)';

        tempCtx.drawImage(
            baseCanvas,
            x,
            y,
            width,
            height,
            0,
            0,
            width,
            height
        );

        ctx.drawImage(
            temp,
            0,
            0,
            width,
            height,
            x,
            y,
            width,
            height
        );

    }

    else if (redaction.tool === 'pixelate') {

        pixelateArea(
            x,
            y,
            width,
            height
        );

    }

    if (preview) {

        ctx.strokeStyle = 'red';
        ctx.lineWidth = 3;
        ctx.setLineDash([8, 6]);

        ctx.strokeRect(
            x,
            y,
            width,
            height
        );

        ctx.setLineDash([]);

    }

}


/* ---------------------------------------------------
   PIXELATE
--------------------------------------------------- */

function pixelateArea(x, y, width, height) {

    const blockSize = 12;

    const temp = document.createElement('canvas');

    temp.width = Math.max(
        1,
        Math.floor(width / blockSize)
    );

    temp.height = Math.max(
        1,
        Math.floor(height / blockSize)
    );

    const tempCtx = temp.getContext('2d');

    tempCtx.imageSmoothingEnabled = false;

    tempCtx.drawImage(
        baseCanvas,
        x,
        y,
        width,
        height,
        0,
        0,
        temp.width,
        temp.height
    );

    ctx.imageSmoothingEnabled = false;

    ctx.drawImage(
        temp,
        0,
        0,
        temp.width,
        temp.height,
        x,
        y,
        width,
        height
    );

    ctx.imageSmoothingEnabled = true;

}


/* ---------------------------------------------------
   SAVE STATE
--------------------------------------------------- */

function saveState() {

    undoStack.push(
        JSON.parse(
            JSON.stringify(redactions)
        )
    );

}


/* ---------------------------------------------------
   UNDO
--------------------------------------------------- */

undoBtn.addEventListener(
    'click',
    function () {

        if (undoStack.length === 0) {
            return;
        }

        redoStack.push(
            JSON.parse(
                JSON.stringify(redactions)
            )
        );

        redactions = undoStack.pop();

        redraw();

        updateHistoryButtons();

        saveImage();

    }
);


/* ---------------------------------------------------
   REDO
--------------------------------------------------- */

redoBtn.addEventListener(
    'click',
    function () {

        if (redoStack.length === 0) {
            return;
        }

        undoStack.push(
            JSON.parse(
                JSON.stringify(redactions)
            )
        );

        redactions = redoStack.pop();

        redraw();

        updateHistoryButtons();

        saveImage();

    }
);


/* ---------------------------------------------------
   CLEAR ALL
--------------------------------------------------- */

clearBtn.addEventListener(
    'click',
    function () {

        if (redactions.length === 0) {
            return;
        }

        saveState();

        redactions = [];

        redoStack = [];

        redraw();

        updateHistoryButtons();

        saveImage();

    }
);


/* ---------------------------------------------------
   HISTORY BUTTONS
--------------------------------------------------- */

function updateHistoryButtons() {

    undoBtn.disabled =
        undoStack.length === 0;

    redoBtn.disabled =
        redoStack.length === 0;

}


function updateZoom() {

    canvas.style.width =
        (canvas.width * zoom) + 'px';

    canvas.style.height =
        (canvas.height * zoom) + 'px';

    zoomLabel.textContent =
        Math.round(zoom * 100) + '%';
}


zoomInBtn.addEventListener('click', function () {

    zoom = Math.min(
        zoom + 0.25,
        3
    );

    updateZoom();

});


zoomOutBtn.addEventListener('click', function () {

    zoom = Math.max(
        zoom - 0.25,
        0.25
    );

    updateZoom();

});


resetZoomBtn.addEventListener('click', function () {

    zoom = 1;

    updateZoom();

});

/* ---------------------------------------------------
   SAVE IMAGE
--------------------------------------------------- */

function saveImage() {

    hiddenInput.value =
        canvas.toDataURL('image/png');

}


/* ---------------------------------------------------
   PREVIEW
--------------------------------------------------- */

previewBtn.addEventListener(
    'click',
    function () {

        saveImage();

        previewImage.src =
            hiddenInput.value;

        const modal =
            new bootstrap.Modal(
                document.getElementById(
                    'previewModal'
                )
            );

        modal.show();

    }
);


/* ---------------------------------------------------
   FORM SUBMIT
--------------------------------------------------- */

document
    .getElementById('redactionForm')
    .addEventListener(
        'submit',
        function () {

            saveImage();

        }
    );

</script>

@endsection