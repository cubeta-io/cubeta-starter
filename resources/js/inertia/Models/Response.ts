export interface PaginatedResponse<T> {
    data: {
        data: T[];
    };
    pagination_date?: {
        currentPage: number;
        from: number;
        to: number;
        total: number;
        per_page: number;
        total_pages: number;
        is_first: boolean;
        is_last: boolean;
    };
}
