<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Platform Policies - CyberGuard</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        h1 {
            margin: 0;
            color: #222;
        }

        .add-btn {
            background: #2563eb;
            color: white;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 6px;
        }

        .add-btn:hover {
            background: #1d4ed8;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            background: #dcfce7;
            color: #166534;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            overflow-x: auto;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        th {
            background: #f8fafc;
            color: #374151;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .current {
            background: #dcfce7;
            color: #166534;
        }

        .review {
            background: #fef3c7;
            color: #92400e;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .edit-btn {
            background: #2563eb;
            color: white;
            text-decoration: none;
            padding: 7px 12px;
            border-radius: 5px;
        }

        .delete-btn {
            background: #dc2626;
            color: white;
            border: none;
            padding: 7px 12px;
            border-radius: 5px;
            cursor: pointer;
        }

        .empty {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        a.reporting-link {
            color: #2563eb;
            text-decoration: none;
        }

        a.reporting-link:hover {
            text-decoration: underline;
        }

        .instructions {
            max-width: 300px;
            white-space: pre-wrap;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">
        <div>
            <h1>Platform Policies</h1>
            <p>Manage reporting policies for different platforms.</p>
        </div>

        <a href="{{ route('moderator.platform-policies.create') }}" class="add-btn">
            + Add Platform Policy
        </a>
    </div>

    @if(session('success'))
        <div class="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-container">

        @if($policies->count() > 0)

            <table>

                <thead>
                    <tr>
                        <th>Platform</th>
                        <th>Reporting URL</th>
                        <th>Instructions</th>
                        <th>Last Verified</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($policies as $policy)

                        <tr>

                            <td>
                                <strong>{{ $policy->platform }}</strong>
                            </td>

                            <td>
                                <a
                                    href="{{ $policy->reporting_url }}"
                                    target="_blank"
                                    class="reporting-link"
                                >
                                    Open Reporting Page
                                </a>
                            </td>

                            <td class="instructions">
                                {{ $policy->instructions }}
                            </td>

                            <td>
                                @if($policy->last_verified_at)
                                    {{ $policy->last_verified_at->format('d M Y') }}
                                @else
                                    Not verified
                                @endif
                            </td>

                            <td>

                                @if($policy->needsReview())

                                    <span class="status review">
                                        ⚠ Needs Review
                                    </span>

                                @else

                                    <span class="status current">
                                        ✓ Current
                                    </span>

                                @endif

                            </td>

                            <td>

                                <div class="actions">

                                    <a
                                        href="{{ route('moderator.platform-policies.edit', $policy) }}"
                                        class="edit-btn"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('moderator.platform-policies.destroy', $policy) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this policy?');"
                                    >

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="delete-btn">
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        @else

            <div class="empty">

                <h3>No Platform Policies Yet</h3>

                <p>
                    You haven't added any platform reporting policies.
                </p>

                <a
                    href="{{ route('moderator.platform-policies.create') }}"
                    class="add-btn"
                >
                    Add Your First Policy
                </a>

            </div>

        @endif

    </div>

</div>

</body>
</html>