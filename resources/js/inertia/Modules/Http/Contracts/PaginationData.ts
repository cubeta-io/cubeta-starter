interface PaginationData {
  current_page: number;
  from: number;
  to: number;
  total: number;
  per_page: number;
  total_pages: number;
  is_first_page: boolean;
  is_last_page: boolean;
}

export default PaginationData;
