export const previewFilters = [
    { label: 'Keyword', value: 'Senior Laravel' },
    { label: 'Location', value: 'Da Nang' },
    { label: 'Experience', value: '5+ years' },
    { label: 'Type', value: 'Last 30 days' },
] as const;

export const previewSignalChips = [
    { id: 'role', value: 'Senior Laravel' },
    { id: 'location', value: 'Da Nang' },
] as const;

export const previewResults = [
    {
        id: 'lead-backend-engineer',
        title: 'Lead Backend Engineer',
        company: 'Larasearch Labs',
        location: 'Da Nang',
        workModel: 'Hybrid',
        salary: '$2,500 - $3,400',
        postedAt: '2h ago',
        isNew: true,
        isSaved: false,
    },
    {
        id: 'senior-platform-engineer',
        title: 'Senior Platform Engineer',
        company: 'Signal Stack',
        location: 'Ho Chi Minh City',
        workModel: 'Remote',
        salary: '$3,000 - $4,200',
        postedAt: '5h ago',
        isNew: false,
        isSaved: true,
    },
    {
        id: 'staff-laravel-engineer',
        title: 'Staff Laravel Engineer',
        company: 'Northstar Commerce',
        location: 'Hanoi',
        workModel: 'Hybrid',
        salary: '$2,800 - $3,800',
        postedAt: '1d ago',
        isNew: false,
        isSaved: false,
    },
] as const;
