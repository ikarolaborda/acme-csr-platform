<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Donation Receipt - {{ $donation->donation_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #4F46E5;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 5px;
        }
        
        .subtitle {
            font-size: 14px;
            color: #666;
        }
        
        .receipt-title {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin: 30px 0;
            color: #1F2937;
        }
        
        .receipt-number {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
        }
        
        .donation-details {
            margin: 30px 0;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #E5E7EB;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #374151;
            width: 40%;
        }
        
        .detail-value {
            color: #1F2937;
            width: 60%;
            text-align: right;
        }
        
        .amount-highlight {
            font-size: 18px;
            font-weight: bold;
            color: #059669;
        }
        
        .campaign-section {
            background-color: #F9FAFB;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        
        .campaign-title {
            font-size: 16px;
            font-weight: bold;
            color: #1F2937;
            margin-bottom: 10px;
        }
        
        .campaign-description {
            color: #6B7280;
            font-size: 11px;
        }
        
        .tax-notice {
            background-color: #FEF3C7;
            border: 1px solid #F59E0B;
            padding: 15px;
            margin: 30px 0;
            border-radius: 6px;
        }
        
        .tax-notice-title {
            font-weight: bold;
            color: #92400E;
            margin-bottom: 5px;
        }
        
        .tax-notice-text {
            font-size: 11px;
            color: #92400E;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            font-size: 10px;
            color: #6B7280;
        }
        
        .contact-info {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #9CA3AF;
        }
        
        .thank-you {
            background-color: #ECFDF5;
            border: 1px solid #10B981;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
        }
        
        .thank-you-text {
            font-size: 14px;
            font-weight: 600;
            color: #065F46;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">ACME CSR Platform</div>
        <div class="subtitle">Corporate Social Responsibility Program</div>
    </div>

    <!-- Receipt Title -->
    <div class="receipt-title">DONATION RECEIPT</div>
    <div class="receipt-number">Receipt #{{ $donation->donation_number }}</div>

    <!-- Thank You Message -->
    <div class="thank-you">
        <div class="thank-you-text">
            Thank you for your generous contribution to {{ $donation->campaign->title }}!
        </div>
    </div>

    <!-- Campaign Information -->
    <div class="campaign-section">
        <div class="campaign-title">{{ $donation->campaign->title }}</div>
        <div class="campaign-description">
            Category: {{ ucfirst($donation->campaign->category) }}<br>
            @if($donation->campaign->short_description)
                {{ $donation->campaign->short_description }}
            @endif
        </div>
    </div>

    <!-- Donation Details -->
    <div class="donation-details">
        <div class="detail-row">
            <div class="detail-label">Donor Name:</div>
            <div class="detail-value">
                @if($donation->is_anonymous)
                    Anonymous Donor
                @else
                    {{ $donation->user ? $donation->user->name : $donation->donor_name }}
                @endif
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Donation Amount:</div>
            <div class="detail-value amount-highlight">${{ number_format($donation->amount, 2) }} {{ $donation->currency }}</div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Date:</div>
            <div class="detail-value">{{ $donation->created_at->format('F j, Y \a\t g:i A') }}</div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Payment Method:</div>
            <div class="detail-value">{{ ucwords(str_replace('_', ' ', $donation->payment_method)) }}</div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Transaction ID:</div>
            <div class="detail-value">{{ $donation->transaction_id ?: 'N/A' }}</div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Status:</div>
            <div class="detail-value">{{ ucfirst($donation->status) }}</div>
        </div>

        @if($donation->message)
        <div class="detail-row">
            <div class="detail-label">Message:</div>
            <div class="detail-value">"{{ $donation->message }}"</div>
        </div>
        @endif
    </div>

    <!-- Tax Notice -->
    <div class="tax-notice">
        <div class="tax-notice-title">Tax Deductible Donation</div>
        <div class="tax-notice-text">
            This donation may be tax deductible to the extent allowed by law. 
            Please consult with your tax advisor for specific guidance. 
            ACME Corp is a qualified charitable organization under section 501(c)(3) of the Internal Revenue Code.
            <br><br>
            <strong>Tax ID:</strong> 12-3456789 | <strong>Date of Donation:</strong> {{ $donation->created_at->format('F j, Y') }}
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div>
            This receipt was generated on {{ now()->format('F j, Y \a\t g:i A') }} and serves as official documentation of your donation.
        </div>
        <div class="contact-info">
            ACME Corp | 123 Business Street, Corporate City, CC 12345<br>
            Email: donations@acme-corp.com | Phone: (555) 123-4567<br>
            Website: www.acme-corp.com/csr
        </div>
    </div>
</body>
</html> 