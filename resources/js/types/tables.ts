export type DataTableColumn = {
    key: string;
    label: string;
    sortable?: boolean;
    align?: 'left' | 'right' | 'center';
    headerClass?: string;
    cellClass?: string;
};

export type LaravelPaginatorLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type LaravelPaginator<T = Record<string, unknown>> = {
    data: T[];
    links: LaravelPaginatorLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

export type TableFilterOption = {
    value: string;
    label: string;
};

export type TableFilterField = {
    key: string;
    label: string;
    type: 'text' | 'select' | 'date';
    options?: TableFilterOption[];
    placeholder?: string;
};
