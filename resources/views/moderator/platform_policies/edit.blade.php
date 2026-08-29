<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Platform Policy - CyberGuard</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 800px;
            margin: auto;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-top: 0;
            color: #222;
        }

        .description {
            color: #6b7280;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #374151;
        }

        input,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 15px;
            box-sizing: border-box;
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #2563eb;
        }

        .error {
            color: #dc2626;
            font-size: 14px;
            margin-top: 5px;
        }

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .save-btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
        }

        .save-btn:hover {
            background: #1d4ed8;
        }

        .cancel-btn {
            background: #6b7280;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
        }

        .cancel-btn:hover {
            background: #4b5563;
        }

        .required {
            color: #dc2626;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="card">

        <h1>Edit Platform Policy</h1>

        <p class="description">
            Update the reporting information and instructions for this platform.
        </p>

        @if($errors->any())
            <div class="error" style="margin-bottom: 20px;">
                <strong>Please fix the following errors:</strong>

                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ route('moderator.platform-policies.update', $platformPolicy) }}"
            method="POST"
        >

            @csrf
            @method('PUT')

            <div class="form-group">

                <label for="platform">
                    Platform <span class="required">*</span>
                </label>

                <input
                    type="text"
                    id="platform"
                    name="platform"
                    value="{{ old('platform', $platformPolicy->platform) }}"
                    required
                >

                @error('platform')
                    <div class="error">{{ $message }}</div>
                @enderror

            </div>

            <div class="form-group">

                <label for="reporting_url">
                    Reporting URL <span class="required">*</span>
                </label>

                <input
                    type="url"
                    id="reporting_url"
                    name="reporting_url"
                    value="{{ old('reporting_url', $platformPolicy->reporting_url) }}"
                    required
                >

                @error('reporting_url')
                    <div class="error">{{ $message }}</div>
                @enderror

            </div>

            <div class="form-group">

                <label for="instructions">
                    Reporting Instructions <span class="required">*</span>
                </label>

                <textarea
                    id="instructions"
                    name="instructions"
                    required
                >{{ old('instructions', $platformPolicy->instructions) }}</textarea>

                @error('instructions')
                    <div class="error">{{ $message }}</div>
                @enderror

            </div>

            <div class="form-group">

                <label for="last_verified_at">
                    Last Verified Date
                </label>

                <input
                    type="date"
                    id="last_verified_at"
                    name="last_verified_at"
                    value="{{ old('last_verified_at', $platformPolicy->last_verified_at?->format('Y-m-d')) }}"
                >

                @error('last_verified_at')
                    <div class="error">{{ $message }}</div>
                @enderror

            </div>

            <div class="buttons">

                <button type="submit" class="save-btn">
                    Update Policy
                </button>

                <a
                    href="{{ route('moderator.platform-policies.index') }}"
                    class="cancel-btn"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>