export type SearchFilterChip = {
    id: string;
    label: string;
    value: string;
};

export type SearchFilterField = {
    id: string;
    label: string;
    value: string;
    options?: string[];
};

export type SearchResult = {
    id: string;
    title: string;
    company: string;
    location: string;
    workModel: string;
    salary: string;
    experience: string;
    postedAt: string;
    isNew?: boolean;
    isSaved?: boolean;
    matchRate: string;
    overview: string;
    companySummary: string;
    companyMeta: string;
    workType: string;
    officeLocation: string;
    mapLabel: string;
    benchmark: string;
    responsibilities: string[];
    requirements: {
        label: string;
        value: string;
    }[];
    summaryMetrics: {
        label: string;
        value: string;
    }[];
    skills: string[];
};

export const searchFilters: SearchFilterField[] = [
    { id: 'keyword', label: 'Keyword', value: 'Principal Architect' },
    { id: 'location', label: 'Location', value: 'Remote, NYC...' },
    {
        id: 'work-model',
        label: 'Work Model',
        value: 'All Models',
        options: ['All Models', 'Remote', 'Hybrid', 'On-site'],
    },
    {
        id: 'experience',
        label: 'Experience',
        value: 'All Levels',
        options: ['All Levels', 'Senior', 'Lead', 'Director'],
    },
    {
        id: 'salary',
        label: 'Min Salary',
        value: '$120k+',
        options: ['$120k+', '$150k+', '$180k+', '$220k+'],
    },
];

export const activeFilterChips: SearchFilterChip[] = [
    { id: 'role', label: 'Role', value: 'Principal Architect' },
    { id: 'remote', label: 'Remote', value: 'Global' },
];

export const searchResults: SearchResult[] = [
    {
        id: 'lead-technical-architect',
        title: 'Lead Technical Architect',
        company: 'Lumiere Structural Group',
        location: 'Zurich, CH',
        workModel: 'Hybrid',
        salary: '$140k — $185k',
        experience: '8+ Years',
        postedAt: '2h ago',
        isNew: true,
        matchRate: '98%',
        overview:
            'Lumiere is seeking a lead technical architect to translate ambitious high-rise concepts into buildable structural systems across a sustainable Zurich portfolio.',
        companySummary:
            'A globally distributed structural and design consultancy focused on civic-scale mixed-use developments with deep computational practice.',
        companyMeta: 'Global Headquarters • 5,000+ Employees',
        workType: 'Full-Time / Permanent',
        officeLocation: 'Hybrid (Zurich)',
        mapLabel: 'Lakefront District, Zurich',
        benchmark:
            'Your profile aligns with 94% of top performers in this role based on Larasearch benchmark signals.',
        responsibilities: [
            'Lead technical design phases for landmark mixed-use projects exceeding CHF 180M in value.',
            'Translate conceptual architecture into deliverable structural systems across interdisciplinary teams.',
            'Drive computational design workflows spanning Rhino, Grasshopper, BIM, and sustainability analytics.',
            'Mentor senior architects and engineering leads on review quality, documentation rigor, and delivery standards.',
        ],
        requirements: [
            {
                label: 'Certification',
                value: 'Registered Architect or equivalent EU charter status',
            },
            {
                label: 'Experience',
                value: '8+ years leading complex technical architecture programs',
            },
            {
                label: 'Software',
                value: 'Rhino, Grasshopper, BIM Level 3, Python/C#',
            },
            {
                label: 'Education',
                value: 'Masters in Architecture, Structural Design, or similar',
            },
        ],
        summaryMetrics: [
            { label: 'Annual Salary', value: '$140,000 — $185,000' },
            { label: 'Experience Required', value: '8+ Years' },
            { label: 'Work Location', value: 'Hybrid (Zurich)' },
            { label: 'Job Type', value: 'Full-Time / Permanent' },
        ],
        skills: [
            'Rhino/Grasshopper',
            'BIM Level 3',
            'Sustainability Analytics',
            'Python / C#',
            'Structural Engineering',
        ],
    },
    {
        id: 'senior-bim-manager',
        title: 'Senior BIM Manager',
        company: 'Aethelred Partners',
        location: 'London, UK',
        workModel: 'Hybrid',
        salary: '$110k — $135k',
        experience: 'Senior+',
        postedAt: '6h ago',
        isSaved: true,
        matchRate: '82%',
        overview:
            'Own BIM governance, cross-discipline coordination, and delivery standards for a fast-moving international property program.',
        companySummary:
            'A design and advisory group operating across transport, workplace, and public-sector transformation.',
        companyMeta: 'London Studio • 1,200 Employees',
        workType: 'Full-Time',
        officeLocation: 'Hybrid (London)',
        mapLabel: 'Farringdon Studio Cluster',
        benchmark:
            'Your current toolchain overlaps with 86% of the systems used by high-performing BIM managers in comparable firms.',
        responsibilities: [
            'Govern federated BIM models across architecture, engineering, and construction teams.',
            'Set QA standards, naming conventions, and delivery calendars.',
            'Lead stakeholder workshops for clash resolution and model health audits.',
            'Coach project teams on standards adoption and digital delivery maturity.',
        ],
        requirements: [
            {
                label: 'Certification',
                value: 'BIM leadership accreditation preferred',
            },
            { label: 'Experience', value: '7+ years in BIM operations' },
            { label: 'Software', value: 'Revit, Navisworks, BIM360, Solibri' },
            {
                label: 'Education',
                value: 'Built environment or digital engineering discipline',
            },
        ],
        summaryMetrics: [
            { label: 'Annual Salary', value: '$110,000 — $135,000' },
            { label: 'Experience Required', value: 'Senior+' },
            { label: 'Work Location', value: 'Hybrid (London)' },
            { label: 'Job Type', value: 'Full-Time' },
        ],
        skills: [
            'Revit',
            'Navisworks',
            'Model QA',
            'Digital Delivery',
            'Team Coaching',
        ],
    },
    {
        id: 'principal-design-strategist',
        title: 'Principal Design Strategist',
        company: 'Nexus Urban Labs',
        location: 'San Francisco, CA',
        workModel: 'Remote',
        salary: '$165k — $210k',
        experience: '10+ Years',
        postedAt: '1d ago',
        matchRate: '75%',
        overview:
            'Guide productized urban systems strategy across research, market positioning, and cross-functional design leadership.',
        companySummary:
            'An urban innovation studio building data-informed systems for resilient public infrastructure and civic platforms.',
        companyMeta: 'West Coast Hub • 400 Employees',
        workType: 'Remote',
        officeLocation: 'Remote / SF',
        mapLabel: 'Mission Bay Research District',
        benchmark:
            'Your profile is strongest on systems thinking and stakeholder alignment; compensation trends suggest upside at enterprise-scale firms.',
        responsibilities: [
            'Define multi-year product and design strategy for urban systems initiatives.',
            'Lead executive workshops and translate research into delivery roadmaps.',
            'Shape talent, operating rhythm, and design governance across teams.',
            'Partner with data, policy, and engineering on market-facing proposals.',
        ],
        requirements: [
            { label: 'Certification', value: 'Not required' },
            {
                label: 'Experience',
                value: '10+ years in strategic design leadership',
            },
            {
                label: 'Software',
                value: 'Figma, Miro, Notion, analytics tooling',
            },
            {
                label: 'Education',
                value: 'Advanced degree in design, policy, or systems practice preferred',
            },
        ],
        summaryMetrics: [
            { label: 'Annual Salary', value: '$165,000 — $210,000' },
            { label: 'Experience Required', value: '10+ Years' },
            { label: 'Work Location', value: 'Remote / SF' },
            { label: 'Job Type', value: 'Full-Time' },
        ],
        skills: [
            'Strategic Design',
            'Facilitation',
            'Service Systems',
            'Research Ops',
            'Storytelling',
        ],
    },
    {
        id: 'computational-designer',
        title: 'Computational Designer',
        company: 'Onyx Modular Systems',
        location: 'Berlin, DE',
        workModel: 'On-site',
        salary: '$95k — $120k',
        experience: '5+ Years',
        postedAt: '3d ago',
        matchRate: '68%',
        overview:
            'Develop procedural design workflows and automation tools supporting modular fabrication and rapid prototyping.',
        companySummary:
            'A modular systems manufacturer blending parametric design with industrialized delivery.',
        companyMeta: 'Berlin Factory • 650 Employees',
        workType: 'Full-Time',
        officeLocation: 'On-site (Berlin)',
        mapLabel: 'Tempelhof Fabrication Campus',
        benchmark:
            'Your scripting depth is above cohort average, but fabrication experience is lighter than the strongest candidates.',
        responsibilities: [
            'Build parametric workflows for modular assemblies and facade systems.',
            'Prototype automation pipelines connecting design and fabrication teams.',
            'Support early-stage concept studies with quick-turn geometry generation.',
            'Standardize reusable scripts and documentation for production teams.',
        ],
        requirements: [
            { label: 'Certification', value: 'Not required' },
            { label: 'Experience', value: '5+ years in computational design' },
            {
                label: 'Software',
                value: 'Grasshopper, Python, Revit, fabrication toolchains',
            },
            {
                label: 'Education',
                value: 'Architecture, computational design, or engineering',
            },
        ],
        summaryMetrics: [
            { label: 'Annual Salary', value: '$95,000 — $120,000' },
            { label: 'Experience Required', value: '5+ Years' },
            { label: 'Work Location', value: 'On-site (Berlin)' },
            { label: 'Job Type', value: 'Full-Time' },
        ],
        skills: [
            'Parametric Systems',
            'Python',
            'Fabrication',
            'Geometry Logic',
            'Revit',
        ],
    },
    {
        id: 'senior-structural-engineer',
        title: 'Senior Structural Engineer',
        company: 'Metropolis Design Group',
        location: 'New York, NY',
        workModel: 'Hybrid',
        salary: '$165k — $195k',
        experience: '8+ Years',
        postedAt: '4d ago',
        matchRate: '91%',
        overview:
            'Lead structural engineering work for urban towers and commercial headquarters while bridging design ambition with engineering reality.',
        companySummary:
            'A global architecture and engineering firm focused on sustainable urban infrastructure and ultra-tall buildings.',
        companyMeta: 'Midtown Campus • 500 - 1,000 Employees',
        workType: 'Full-Time',
        officeLocation: 'Hybrid (3 Days)',
        mapLabel: 'Midtown West, 42nd St Campus',
        benchmark:
            'Your profile matches 94% of top performers in this role based on Larasearch benchmark data.',
        responsibilities: [
            'Lead structural design phases for projects exceeding $150M in valuation.',
            'Perform advanced seismic and wind analysis using proprietary design tooling.',
            'Collaborate directly with lead architects on innovative framing solutions.',
            'Mentor junior and mid-level engineering staff on technical excellence.',
        ],
        requirements: [
            {
                label: 'Certification',
                value: 'P.E. License (NY State required)',
            },
            { label: 'Experience', value: '8+ years in structural design' },
            { label: 'Software', value: 'Revit, SAP2000, ETABS, Grasshopper' },
            {
                label: 'Education',
                value: 'M.S. in Civil or Structural Engineering',
            },
        ],
        summaryMetrics: [
            { label: 'Annual Salary', value: '$165,000 — $195,000' },
            { label: 'Experience Required', value: '8+ Years' },
            { label: 'Work Location', value: 'Hybrid (3 Days)' },
            { label: 'Job Type', value: 'Full-Time' },
        ],
        skills: [
            'Seismic Analysis',
            'Tall Buildings',
            'Grasshopper',
            'Revit',
            'Team Leadership',
        ],
    },
];

export function getSearchJobById(jobId: string): SearchResult | undefined {
    return searchResults.find((job) => job.id === jobId);
}
