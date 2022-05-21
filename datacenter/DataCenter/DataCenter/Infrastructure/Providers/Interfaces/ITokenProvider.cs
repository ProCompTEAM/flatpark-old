namespace DataCenter.Infrastructure.Providers.Interfaces
{
    public interface ITokenProvider
    {
        string GenerateAuthToken();
    }
}
