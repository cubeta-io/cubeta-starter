import ApiResponse from "@/Modules/Http/ApiResponse";

class HTTP {
  private static instance: HTTP | undefined = undefined;
  private baseHeaders = {
    Accept: "application/html",
    "Content-Type": "application/html",
  };

  private constructor() {}

  public static make() {
    if (!this.instance) {
      this.instance = new HTTP();
    }

    return this.instance;
  }

  public getBaseUrl = () => {
    return import.meta.env.APP_URL;
  }

  public headers = (headers: Record<string, string>) => {
    this.baseHeaders = { ...this.baseHeaders, ...headers };
    return this;
  };

  public get = async <RESPONSE>(
    url: string,
    params?: Record<string, any>,
    headers?: Record<string, string>,
  ): Promise<ApiResponse<RESPONSE>> => {
    return await this.run("GET", url, headers, params, undefined);
  };

  public post = async <RESPONSE>(
    url: string,
    data: Record<string, any> = {},
    headers?: Record<string, string>,
  ): Promise<ApiResponse<RESPONSE>> => {
    return await this.run("POST", url, headers, undefined, data);
  };

  public delete = async (url: string, headers?: Record<string, string>) => {
    return await this.run("DELETE", url, headers, undefined);
  };

  public put = async (
    url: string,
    data: Record<string, any> = {},
    headers?: Record<string, string>,
  ) => {
    return await this.run("PUT", url, headers, undefined, data);
  };

  private run = async (
    method: string,
    url: string,
    headers?: Record<string, string>,
    params?: string | string[][] | Record<string, string> | URLSearchParams,
    data?: Record<string, any> | undefined,
  ): Promise<ApiResponse<any>> => {
    if (params) {
      url = url + new URLSearchParams(params);
    }
    url = this.getUrl(url);
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
      resData.paginate,
    );
  };

  private getUrl = (url: string) => {
    try {
      const parsedUrl = new URL(this.getBaseUrl() + url);
      parsedUrl.pathname = parsedUrl.pathname.replace(/\/{2,}/g, "/");
      return parsedUrl.toString();
    } catch (e) {
      console.error(e);
      return "";
    }
  };
}

export default HTTP;
