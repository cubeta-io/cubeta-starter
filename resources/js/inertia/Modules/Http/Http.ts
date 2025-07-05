import ApiResponse from "@/Modules/Http/ApiResponse";

class HTTP<RESPONSE extends any = any> {
  private static instance: HTTP | undefined = undefined;
  private baseHeaders = {
    Accept: "application/html",
    "Content-Type": "application/json",
    "Accept-Language": window.localStorage.getItem("locale") ?? "en",
  };

  private isFile = false;

  private constructor() {}

  public static make<T extends any = any>(): HTTP<T> {
    if (!this.instance) {
      this.instance = new HTTP<T>();
    }

    this.instance.isFile = false;

    return this.instance as HTTP<T>;
  }

  public headers = (headers: Record<string, string>) => {
    this.baseHeaders = { ...this.baseHeaders, ...headers };
    return this;
  };

  public file() {
    this.isFile = true;
    return this;
  }

  public async get(
    url: string,
    params?: Record<string, any>,
    headers?: Record<string, string>,
  ): Promise<ApiResponse<RESPONSE | undefined>>;

  public async get(
    url: string,
    params?: Record<string, any>,
    headers?: Record<string, string>,
  ): Promise<Response>;

  public async get(
    url: string,
    params?: Record<string, any>,
    headers?: Record<string, string>,
  ): Promise<ApiResponse<RESPONSE | undefined> | Response> {
    return await this.run("GET", url, headers, params, undefined);
  }

  public async post(
    url: string,
    data: Record<string, any>,
    headers?: Record<string, string>,
  ): Promise<ApiResponse<RESPONSE | undefined>>;

  public async post(
    url: string,
    data: Record<string, any>,
    headers?: Record<string, string>,
  ): Promise<Response>;

  public async post(
    url: string,
    data: Record<string, any> = {},
    headers?: Record<string, string>,
  ): Promise<ApiResponse<RESPONSE | undefined> | Response> {
    return await this.run("POST", url, headers, undefined, data);
  }

  public async delete(
    url: string,
    headers?: Record<string, string>,
  ): Promise<ApiResponse<RESPONSE | undefined>>;

  public async delete(
    url: string,
    headers?: Record<string, string>,
  ): Promise<Response>;

  public async delete(
    url: string,
    headers?: Record<string, string>,
  ): Promise<ApiResponse<RESPONSE | undefined> | Response> {
    return await this.run("DELETE", url, headers, undefined);
  }

  public async put(
    url: string,
    data: Record<string, any>,
    headers?: Record<string, string>,
  ): Promise<ApiResponse<RESPONSE | undefined>>;

  public async put(
    url: string,
    data: Record<string, any>,
    headers?: Record<string, string>,
  ): Promise<Response>;

  public async put(
    url: string,
    data: Record<string, any> = {},
    headers?: Record<string, string>,
  ): Promise<ApiResponse<RESPONSE | undefined> | Response> {
    data = { ...data, _method: "PUT" };
    return await this.run("PUT", url, headers, undefined, data);
  }

  private run(
    method: string,
    url: string,
    headers?: Record<string, string>,
    params?: string | string[][] | Record<string, string> | URLSearchParams,
    data?: Record<string, any> | undefined,
  ): Promise<Response>;
  private run(
    method: string,
    url: string,
    headers?: Record<string, string>,
    params?: string | string[][] | Record<string, string> | URLSearchParams,
    data?: Record<string, any> | undefined,
  ): Promise<ApiResponse<RESPONSE>>;

  private async run<T>(
    method: string,
    url: string,
    headers?: Record<string, string>,
    params?: string | string[][] | Record<string, string> | URLSearchParams,
    data?: Record<string, any> | undefined,
  ): Promise<ApiResponse<T> | Response> {
    try {
      url = this.addParamsToUrl(params, url);
      url = this.getUrl(url);

      this.addCsrfIfNeeded(method);

      const request = async () =>
        await fetch(url, {
          method: method,
          headers: {
            ...headers,
            ...this.baseHeaders,
          },
          body: JSON.stringify(data),
        });

      let response = await request();

      if (this.isFile) {
        return response;
      }

      let resData = await response.json();

      if (!response.ok) {
        console.log("Error : " + resData.message);
        console.log("Data : ", resData.data);
        return new ApiResponse(
          undefined,
          false,
          response.status,
          resData.message,
          undefined,
        );
      }

      return new ApiResponse(
        resData.data,
        resData.status,
        resData.code,
        resData.message,
        resData.pagination_data,
      );
    } catch (e) {
      console.error(e);
      console.error("Happened while requesting this url : " + url);
      return new ApiResponse(undefined, false, 500, "Client error", undefined);
    }
  }

  private addCsrfIfNeeded(method: string) {
    if (method == "POST" || method == "PUT" || method == "DELETE") {
      this.headers({
        "X-CSRF-TOKEN": this.csrfToken() ?? "",
      });
    }
  }

  private getUrl = (url: string) => {
    try {
      const parsedUrl = new URL(url);
      parsedUrl.pathname = parsedUrl.pathname.replace(/\/{2,}/g, "/");
      return parsedUrl.toString();
    } catch (e) {
      console.error(e);
      return "";
    }
  };

  public csrfToken = (): string | undefined | null => {
    return document
      ?.querySelector('meta[name="csrf-token"]')
      ?.getAttribute("content");
  };

  private addParamsToUrl(
    params:
      | string
      | string[][]
      | Record<string, string>
      | URLSearchParams
      | undefined,
    url: string,
  ) {
    if (
      params &&
      typeof params === "object" &&
      !(params instanceof URLSearchParams)
    ) {
      params = Object.fromEntries(
        Object.entries(params).filter(([_, value]) => value !== undefined),
      );
      url = url + "?" + new URLSearchParams(params as Record<string, string>);
    }
    return url;
  }
}

export default HTTP;
