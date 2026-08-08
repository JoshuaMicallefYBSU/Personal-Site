<?php

// Everything on the personal site is driven from this file.
// To update your resume, just edit the values below — no template changes needed.

return [

    'name' => 'Joshua Micallef',
    'title' => 'Duty Manager', // your general professional title — this is not dev-specific, it's the headline for your whole career
    'tagline' => '',
    'location' => 'Sunshine Coast, QLD',

    // Put your photo at public/images/profile.jpg and it will be used automatically.
    // Until then, a circle with your initials is shown instead.
    'avatar' => '/images/profile.jpg',

    // Set to a path (e.g. '/files/resume.pdf') to show a "Download Resume" button. Leave null to hide it.
    'resume_url' => null,

    'about' => 'I\'m Joshua, based on the Sunshine Coast, with a career built across hospitality, public transport and technology leadership. I care about doing things properly — whether that\'s running a busy service floor, keeping a bus route on schedule, or standing up reliable infrastructure. Outside of work, I\'m an avid aviation enthusiast and an entry-level developer who enjoys building side projects in my spare time.',

    'social' => [
        ['label' => 'GitHub', 'icon' => 'github', 'url' => 'https://github.com/JoshuaMicallefYBSU'],
        ['label' => 'LinkedIn', 'icon' => 'linkedin', 'url' => 'https://www.linkedin.com/in/joshua-micallef-1221b220a/'],
    ],

    // Where the "Get in touch" form sends its messages. Actual delivery depends on the MAIL_*
    // settings in .env (currently MAIL_MAILER=log, so submissions are written to the log file
    // rather than actually emailed until you configure a real mail driver).
    'contact_email' => 'you@example.com',

    'experience' => [
        [
            'role' => 'Duty Manager',
            'company' => 'The Coffee Club',
            'location' => 'Cotton Tree, QLD',
            'period' => 'January 2026 — Present',
            'summary' => 'Delivering exceptional customer experiences and maintaining the highest standards of service excellence.',
            'highlights' => [
                'Strong organisational and multitasking abilities, with the capacity to manage a busy cafe during peak times',
                'Excellent customer service orientation with a track record of building strong customer relationships',
                'Knowledge of health, safety and food hygiene regulations relevant to hospitality venues',
                'Proficiency in point-of-sale systems, hospitality management training & ordering systems',
            ],
        ],
        [
            'role' => 'Bus Operator',
            'company' => 'Brisbane City Council',
            'location' => 'Sherwood, QLD',
            'period' => 'March 2024 — December 2025',
            'summary' => 'Provided on-time public transport services for the city of Brisbane on behalf of Translink',
            'highlights' => [
                'Safely operated public buses across metropolitan Brisbane routes, maintaining strict adherence to timetables and road safety regulations',
                'Delivered consistent, professional customer service to a diverse range of passengers daily',
                'Managed fare collection and Translink ticketing procedures accurately under time pressure',
                'Completed mandatory heavy vehicle licensing and ongoing safety and compliance training',
            ],
        ],
                [
            'role' => 'Chief Technology Officer (CTO)',
            'company' => 'Horizon Tutoring Co.',
            'location' => 'Brisbane, QLD',
            'period' => 'Feburary 2023 — December 2023',
            'summary' => 'Responsible for managing tutoring infrastructure, including the SSO, Dashboard and Video-Conference infrastructure.',
            'highlights' => [
                'Owned the design, deployment and maintenance of the company\'s Single Sign-On (SSO), dashboard and video-conferencing infrastructure',
                'Led technical decision-making for the platform, balancing reliability, security and cost',
                'Worked closely with non-technical stakeholders to translate business needs into practical technical solutions',
                'Managed uptime and troubleshooting to keep tutoring sessions running without interruption',
            ],
        ],
        [
            'role' => 'Service Team Member & Manager',
            'company' => 'Coles Group',
            'location' => 'Woolloongabba, QLD',
            'period' => 'December 2021 — March 2024',
            'summary' => 'Customer Service Team Member, responsible for ensuring customer satisfaction and assiting customers whenever and whereever required.',
            'highlights' => [
                'Balanced front-line customer service duties with team leadership and supervisory responsibilities',
                'Trained and supported new team members on store procedures and customer service standards',
                'Handled customer queries, complaints and returns in line with company policy',
                'Assisted with day-to-day store operations, including stock management and point-of-sale duties',
            ],
        ],
        [
            'role' => 'Waiter',
            'company' => 'The Coffee Club',
            'location' => 'Cotton Tree, QLD',
            'period' => 'June 2017 — October 2021',
            'summary' => 'Delivering exceptional customer experiences and maintaining the highest standards of service excellence',
            'highlights' => [
                'Point-of-sale (POS) system experience',
                'A passion for delivering outstanding customer service and creating a positive cafe experience',
                'Reliability, punctuality, and a professional attitude towards work'
            ],
        ],
    ],

    'certifications' => [
        [
            'name' => 'Responsible Service of Alcohol (RSA)',
            'issuer' => 'Prestige Service Training.',
            'date' => 'August 2020',
            'url' => null,
        ],
        [
            'name' => 'First Aid Certificate',
            'issuer' => "St John's Ambulance Services",
            'date' => 'April 2023',
            'url' => null,
        ],
        [
            'name' => 'Certificate III in Driving Operations (LTI31222)',
            'issuer' => "TAFE Queensland",
            'date' => 'November 2025',
            'url' => null,
        ],
    ],

    // General professional/soft skills first, technical skills after — this section covers your whole
    // skillset, not just the development side. Add, remove, or rename categories freely.
    'skills' => [
        'Professional' => ['Team Leadership', 'Customer Service', 'Staff Training & Onboarding', 'Stakeholder Management', 'Multitasking Under Pressure'],
        'Technical' => ['Infrastructure Management', 'Single Sign-On (SSO) Systems', 'Video Conferencing Platforms', 'Point-of-Sale (POS) Systems'],
        'Tools' => ['Git', 'Laravel', 'PHP', 'MySQL', 'Docker'],
    ],

    // This is the one section of the site that's specifically about your development side projects —
    // everything above it (experience, certifications, skills) is your general professional resume.
    'projects_intro' => 'Outside of work, I like building things. A few of the projects I\'ve worked on in my own time:',
    'projects' => [
        [
            'name' => 'OzBays - Alpha Release',
            'description' => 'Realistic Bay Assignment Emulator for the VATSIM Network',
            'tech' => ['Laravel', 'Tailwind CSS', 'PHP'],
            'started' => 'December 2025', // month, year the project began
            'url' => 'https://ozbays.xyz', // live website, e.g. 'https://example.com'
            'repo' => 'https://github.com/JoshuaMicallefYBSU/OzBays', // GitHub repo, e.g. 'https://github.com/your-username/project'
        ],
        [
            'name' => 'OzPAX - In Development',
            'description' => 'A passenger emulator for the VATSIM Network inside VATSIM Australia Pacific (VATPAC) Airspace.',
            'tech' => ['PHP', 'MySQL'],
            'started' => 'June 2026',
            'url' => 'https://ozpax.org',
            'repo' => null,
        ],
        [
            'name' => 'OzServer - In Development',
            'description' => "OzServer is a central authority server that every controller's vatSys client connects to, keeping sector ownership and tag data consistent across the whole network — instead of every client deciding for itself.",
            'tech' => ['PHP', 'MySQL'],
            'started' => 'July 2026',
            'url' => 'https://ozserver.org',
            'repo' => null,
        ],
        [
            'name' => 'Horizon Tutoring Co.',
            'description' => 'A passenger emulator for the VATSIM Network inside VATSIM Australia Pacific (VATPAC) Airspace.',
            'tech' => ['PHP', 'MySQL'],
            'started' => 'April 2023',
            'url' => 'https://horizontutoring.com.au',
            'repo' => null,
        ],
    ],

];