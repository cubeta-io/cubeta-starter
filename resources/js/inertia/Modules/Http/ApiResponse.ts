import PaginationData from "@/Modules/Http/Contracts/PaginationData";

class ApiResponse<T> {
  private readonly _data: T | undefined;
  private readonly _message: string | undefined;
  private readonly _code: number;
  private readonly _status: boolean;
  private readonly _paginate: PaginationData | undefined;

  constructor(
    data: T | undefined,
    status = true,
    code = 500,
    message: string | undefined = undefined,
    paginate: PaginationData | undefined = undefined,
  ) {
    this._data = data;
    this._status = status;
    this._code = code;
    this._message = message;
    this._paginate = paginate;
  }

  get data(): T | undefined {
    return this._data;
  }

  get message(): string | undefined {
    return this._message;
  }

  get code(): number {
    return this._code;
  }

  get status(): boolean {
    return this._status;
  }

  get paginate(): PaginationData | undefined {
    return this._paginate;
  }

  public ok() {
    return this.code == 200;
  }

  public validationError() {
    return this.code == 422;
  }

  public unAuthorized() {
    return this.code == 401;
  }

  public notFound() {
    return this.code == 404;
  }
}

export default ApiResponse;
